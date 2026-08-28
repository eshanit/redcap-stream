<?php

namespace App\Services;

use App\Models\Data6Encounter;
use App\Models\Data6Patient;
use App\Models\Data6SourceRecord;
use App\Models\ProjectData6;
use App\Models\ProjectEventMetadata;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProjectData6Service
{
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

    public function rows(?array $projectIds = null): Builder
    {
        $projectIds ??= $this->data6ProjectIds();

        return ProjectData6::query()->forProjects($projectIds);
    }

    public function recordRows(string $record, ?array $projectIds = null): Builder
    {
        return $this->rows($projectIds)->forRecord($record);
    }

    public function uniqueRecordCount(?array $projectIds = null): int
    {
        return $this->rows($projectIds)
            ->distinct('record')
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

    public function timeline(Data6Patient $patient)
    {
        return Data6Encounter::query()
            ->whereHas('sourceRecord.patients', fn ($query) => $query->whereKey($patient->id))
            ->orderByRaw('service_date IS NULL')
            ->orderBy('service_date')
            ->orderBy('project_id')
            ->orderBy('instrument')
            ->orderBy('normalized_instance')
            ->get();
    }

    public function linkSourceRecord(
        Data6Patient $patient,
        Data6SourceRecord $sourceRecord,
        string $matchMethod,
        ?float $confidence = null,
    ): void {
        abort_unless(in_array($sourceRecord->project_id, $this->data6ProjectIds(), true), 404);

        $patient->sourceRecords()->syncWithoutDetaching([
            $sourceRecord->id => [
                'match_method' => $matchMethod,
                'match_confidence' => $confidence,
                'review_status' => 'reviewed',
            ],
        ]);
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