<?php

namespace App\Services;

use App\Models\Data6Encounter;
use App\Models\Data6Patient;
use App\Models\Data6SourceRecord;
use App\Models\ProjectEventMetadata;
use App\Services\Data6\QueryFragments;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class ProjectData6Service
{
    use QueryFragments;

    // demogSql() needs these declared; serviceReport() applies its own
    // facility/date filters directly rather than through these.
    private ?string $district = null;

    private ?string $facility = null;

    private ?string $gender = null;

    private const INSTRUMENTS = [
        'demog' => 'Demographics',
        'sti' => 'STI',
        'prepr' => 'PrEP Initial',
        'prep' => 'PrEP Follow-up',
        'mh' => 'Mental Health',
        'he' => 'Health Education',
        'couns' => 'Counselling',
        'pls' => 'Peer Support',
        'hts' => 'HIV Testing',
        'fp' => 'Family Planning',
        'ancr' => 'ANC Initial',
        'anc' => 'ANC Follow-up',
        'pncr' => 'PNC Initial',
        'pncm' => 'PNC Mother Follow-up',
        'pncb' => 'PNC Baby Follow-up',
        'artr' => 'OI/ART Initial Register',
        'artib' => 'OI/ART Initial Baseline',
        'art' => 'OI/ART Follow-up',
        'opd' => 'OPD',
    ];

    private const DATE_FIELDS = [
        'sti' => ['sti_visit_date', 'sti_date'],
        'fp' => ['fp_date'],
        'anc' => ['anc_date'],
        'ancr' => ['ancr_date'],
        'pncr' => ['pncr_date'],
        'pncm' => ['pncm_visit_date'],
        'pncb' => ['pncb_visit_date'],
        'prepr' => ['prepr_date'],
        'prep' => ['prep_visit_date'],
        'artr' => ['artr_registration_date'],
        'art' => ['art_review_date'],
        'pls' => ['pls_date'],
        'hts' => ['hts_hiv_date'],
        'opd' => ['opd_date'],
    ];

    public function data6ProjectIds(): array
    {
        return config('redcap.data6_unit.project_ids', array_values(config('redcap.data6_projects', [])));
    }

    /**
     * Every project this reporting unit draws from: the service projects
     * plus the centralised demographics-only project (82) - as opposed to
     * data6ProjectIds(), which stays service-projects-only since it drives
     * the selectable project tabs/filter and 82 has no services to show a
     * tab for. Used for the unit's total record count and by data6:sync.
     */
    public function allProjectIds(): array
    {
        return [...$this->data6ProjectIds(), ...config('redcap.data6_unit.demographics_project_ids', [82])];
    }

    /** Which physical table a project's long-format rows live in - see allProjectIds()'s docblock. */
    private function tableFor(int $projectId): string
    {
        return in_array($projectId, config('redcap.data6_unit.demographics_project_ids', [82]), true)
            ? 'redcap_data5'
            : 'redcap_data6';
    }

    /**
     * Every long-format row for the given projects, as one flat builder -
     * regardless of which physical table each project's data actually
     * lives in (see tableFor()). Projects sharing a table are queried
     * together with a plain WHERE; projects split across tables are each
     * queried separately and combined with UNION ALL. The common case -
     * one project, as used throughout syncRecord() - stays a single plain
     * query with no union/subquery overhead.
     */
    public function rows(?array $projectIds = null): Builder
    {
        $projectIds ??= $this->allProjectIds();

        $byTable = [];
        foreach ($projectIds as $id) {
            $byTable[$this->tableFor((int) $id)][] = (int) $id;
        }

        $queries = array_map(
            fn ($table, $ids) => DB::table($table)
                ->select('project_id', 'record', 'field_name', 'value', 'event_id', 'instance')
                ->whereIn('project_id', $ids),
            array_keys($byTable),
            $byTable,
        );

        if (count($queries) === 1) {
            return $queries[0];
        }

        $combined = array_shift($queries);
        foreach ($queries as $query) {
            $combined->unionAll($query);
        }

        // Wrapped as a derived table so callers can freely chain their own
        // select/where/groupBy afterwards (e.g. recordsByProject()) exactly
        // as they could against a single real table - a raw UNION ALL
        // can't safely take further clauses appended directly onto it.
        return DB::query()->fromSub($combined, 'rows');
    }

    public function recordRows(string $record, ?array $projectIds = null): Builder
    {
        return $this->rows($projectIds)->where('record', $record);
    }

    public function uniqueRecordCount(?array $projectIds = null): int
    {
        return $this->rows($projectIds)
            ->distinct()
            ->count('record');
    }

    public function recordsByProject(?array $projectIds = null): array
    {
        return $this->rows($projectIds)
            ->select('project_id')
            ->selectRaw('COUNT(DISTINCT record) AS record_count')
            ->groupBy('project_id')
            ->pluck('record_count', 'project_id')
            ->map(fn ($count): int => (int) $count)
            ->all();
    }

    public function syncRecord(int $projectId, string $record): Data6Patient
    {
        return DB::transaction(function () use ($projectId, $record) {
            $sourceRecord = Data6SourceRecord::query()->firstOrCreate([
                'project_id' => $projectId,
                'redcap_record' => $record,
            ]);

            $patient = $sourceRecord->patients()->first() ?? Data6Patient::query()->create();

            $patient->sourceRecords()->syncWithoutDetaching([
                $sourceRecord->id => [
                    'match_method' => 'source_record',
                    'match_confidence' => null,
                    'review_status' => 'pending',
                ],
            ]);

            $rows = $this->recordRows($record, [$projectId])->get();
            $groups = $rows->filter(fn ($row) => $this->instrumentKey($row->field_name) !== null)
                ->groupBy(fn ($row) => implode('|', [
                    $row->event_id ?? 'null',
                    $this->instrumentKey($row->field_name),
                    $this->normalizedInstance($row->instance),
                ]));

            foreach ($groups as $group) {
                $first = $group->first();
                $instrumentKey = $this->instrumentKey($first->field_name);
                $rawInstance = $first->instance === '' ? null : $first->instance;
                $normalizedInstance = $this->normalizedInstance($rawInstance);
                $date = $this->serviceDate($group, $instrumentKey);
                $eventId = $first->event_id === null ? null : (int) $first->event_id;
                $armId = $eventId === null ? null : ProjectEventMetadata::query()
                    ->where('event_id', $eventId)
                    ->value('arm_id');
                $sourceKey = hash('sha256', implode('|', [
                    $projectId,
                    $record,
                    $eventId ?? 'null',
                    $instrumentKey,
                    $rawInstance ?? 'null',
                ]));

                Data6Encounter::query()->updateOrCreate(
                    ['source_key' => $sourceKey],
                    [
                        'source_record_id' => $sourceRecord->id,
                        'project_id' => $projectId,
                        'redcap_record' => $record,
                        'event_id' => $eventId,
                        'arm_id' => $armId,
                        'facility' => $rows->firstWhere('field_name', 'demog_facility')?->value,
                        'instrument' => $instrumentKey,
                        'service' => self::INSTRUMENTS[$instrumentKey],
                        'subject_type' => $this->subjectType($instrumentKey),
                        'raw_instance' => $rawInstance === null ? null : (int) $rawInstance,
                        'normalized_instance' => $normalizedInstance,
                        'service_date' => $date['value'],
                        'date_source' => $date['source'],
                        'source_fields' => $group->pluck('field_name')->unique()->values()->all(),
                    ]
                );
            }

            return $patient->load('sourceRecords.encounters');
        });
    }

    /**
     * Unique patients by facility and service, straight off redcap_data6 -
     * facility comes from each client's own demographics, service from the
     * deduplicated, home-project-routed encounter union (encountersSql()),
     * so this needs no separate synced copy of the data.
     */
    public function serviceReport(?array $projectIds = null, ?string $service = null, ?string $facility = null, ?string $from = null, ?string $to = null): array
    {
        [$demogSql, $bind] = $this->demogSql();
        $enc = $this->encountersSql();

        $where = [];
        if ($projectIds !== null) {
            $placeholders = implode(', ', array_fill(0, count($projectIds), '?'));
            $where[] = "e.project_id IN ({$placeholders})";
            $bind = [...$bind, ...$projectIds];
        }
        if ($service !== null) {
            // `service` here is the family label returned by this same
            // report (e.g. "ANC"), which can span several instrument keys
            // (ancr + anc) - match every key whose family resolves to it.
            $instruments = array_keys(array_filter(self::$FAMILY, fn ($label) => $label === $service));
            $where[] = $instruments === []
                ? '1 = 0'
                : 'e.instrument IN ('.implode(', ', array_fill(0, count($instruments), '?')).')';
            $bind = [...$bind, ...$instruments];
        }
        if ($facility !== null) {
            $where[] = "COALESCE(NULLIF(d.facility, ''), 'Unknown') = ?";
            $bind[] = $facility;
        }
        if ($from !== null) {
            $where[] = 'e.visit_date >= ?';
            $bind[] = $from;
        }
        if ($to !== null) {
            $where[] = 'e.visit_date <= ?';
            $bind[] = $to;
        }
        $whereSql = $where === [] ? '' : 'WHERE '.implode(' AND ', $where);

        // Several instrument keys share one family (ancr+anc -> "ANC"), so
        // the family - not the raw instrument - has to be the GROUP BY key,
        // or the same family would be double-counted across two rows.
        $familyCase = $this->familyCaseSql('e.instrument');

        $rows = DB::select("
            WITH demog AS ({$demogSql}), enc AS ({$enc})
            SELECT COALESCE(NULLIF(d.facility, ''), 'Unknown') AS facility,
                   {$familyCase} AS service,
                   COUNT(DISTINCT e.record) AS unique_patients,
                   COUNT(DISTINCT e.project_id) AS projects_represented
            FROM enc e
            JOIN demog d ON d.record = e.record
            {$whereSql}
            GROUP BY facility, service
            ORDER BY facility, service
        ", $bind);

        return array_map(fn ($r) => [
            'facility' => $r->facility,
            'service' => $r->service,
            'unique_patients' => (int) $r->unique_patients,
            'projects_represented' => (int) $r->projects_represented,
        ], $rows);
    }

    private function familyCaseSql(string $column): string
    {
        return "CASE {$column} "
            .implode(' ', array_map(fn ($k, $v) => "WHEN '{$k}' THEN '{$v}'", array_keys(self::$FAMILY), self::$FAMILY))
            ." ELSE {$column} END";
    }

    /**
     * All-time totals per service family for the Service map cards: unique
     * patients, how many facilities report it, which projects it's been
     * recorded under, and the most recent dated activity. Mental health,
     * health education and counselling have no date field on their forms,
     * so they're totalled separately from the dated encounter union and
     * always report a null last_activity.
     */
    public function serviceTotals(): array
    {
        // demogSql() costs ~1s to materialize; this method needs it twice
        // (dated + dateless totals), so build it once into a real temporary
        // table rather than re-embedding it as a CTE in both queries - the
        // same fix applied to ArtCascadeAnalysis/HtsReconciliationAnalysis
        // earlier in this project for the same reason.
        [$demogSql, $bind] = $this->demogSql();
        DB::statement('DROP TEMPORARY TABLE IF EXISTS demog_totals');
        DB::statement("CREATE TEMPORARY TABLE demog_totals AS {$demogSql}", $bind);
        DB::statement('ALTER TABLE demog_totals ADD INDEX (record)');

        $enc = $this->encountersSql();
        $familyCase = $this->familyCaseSql('e.instrument');

        $dated = DB::select("
            WITH enc AS ({$enc})
            SELECT {$familyCase} AS service,
                   COUNT(DISTINCT e.record) AS unique_patients,
                   COUNT(DISTINCT COALESCE(NULLIF(d.facility, ''), 'Unknown')) AS facilities,
                   MAX(e.visit_date) AS last_activity,
                   GROUP_CONCAT(DISTINCT e.project_id ORDER BY e.project_id) AS projects_csv
            FROM enc e
            JOIN demog_totals d ON d.record = e.record
            GROUP BY service
        ");

        $dateless = DB::select("
            SELECT SUBSTRING_INDEX(rd.field_name, '_', 1) AS instrument,
                   COUNT(DISTINCT rd.record) AS unique_patients,
                   COUNT(DISTINCT COALESCE(NULLIF(d.facility, ''), 'Unknown')) AS facilities,
                   GROUP_CONCAT(DISTINCT rd.project_id ORDER BY rd.project_id) AS projects_csv
            FROM redcap_data6 rd
            JOIN demog_totals d ON d.record = rd.record
            WHERE rd.project_id IN (".self::$P_ALL.")
              AND rd.field_name IN ('mh_access', 'he_access', 'couns_access') AND rd.value = 'Y'
            GROUP BY instrument
        ");

        $rows = array_map(fn ($r) => [
            'service' => $r->service,
            'unique_patients' => (int) $r->unique_patients,
            'facilities' => (int) $r->facilities,
            'last_activity' => $r->last_activity,
            'projects' => array_map('intval', explode(',', $r->projects_csv)),
        ], $dated);

        foreach ($dateless as $r) {
            $rows[] = [
                'service' => $this->familyLabel($r->instrument) ?? $r->instrument,
                'unique_patients' => (int) $r->unique_patients,
                'facilities' => (int) $r->facilities,
                'last_activity' => null,
                'projects' => array_map('intval', explode(',', $r->projects_csv)),
            ];
        }

        usort($rows, fn ($a, $b) => $b['unique_patients'] <=> $a['unique_patients']);

        return $rows;
    }

    private function instrumentKey(string $fieldName): ?string
    {
        foreach (array_keys(self::INSTRUMENTS) as $prefix) {
            if ($fieldName === $prefix || str_starts_with($fieldName, $prefix.'_')) {
                return $prefix;
            }
        }

        return null;
    }

    private function normalizedInstance(mixed $instance): int
    {
        return $instance === null || $instance === '' || (int) $instance === 0 ? 1 : (int) $instance;
    }

    private function subjectType(string $instrument): string
    {
        return match ($instrument) {
            'pncm' => 'mother',
            'pncb' => 'baby',
            default => 'client',
        };
    }

    private function serviceDate($rows, string $instrument): array
    {
        foreach (self::DATE_FIELDS[$instrument] ?? [] as $fieldName) {
            $value = $rows->firstWhere('field_name', $fieldName)?->value;
            if (! is_string($value) || trim($value) === '') {
                continue;
            }

            try {
                return [
                    'value' => Carbon::createFromFormat('Y-m-d', trim($value))->format('Y-m-d'),
                    'source' => $fieldName,
                ];
            } catch (\Throwable) {
                return ['value' => null, 'source' => $fieldName];
            }
        }

        return ['value' => null, 'source' => null];
    }
}