<?php

namespace App\Services\Data6;

use Illuminate\Support\Facades\DB;

/**
 * Per-record patient flow: resolves one REDCap `record` to its demographics,
 * which projects it appears in, and a deduplicated chronological timeline of
 * every dated encounter plus the three dateless access flags (mental health,
 * health education, counselling).
 *
 * `record` is already the stable, shared identifier across the three split
 * projects (76/78/79, continuing from project 48) - no separate identity
 * resolution/matching step is needed. Reuses the same `QueryFragments`
 * building blocks (`demogSql`, `encountersSql`) as every other Data6
 * service, so the home-project routing and mirrored-copy dedup rules stay
 * identical everywhere.
 */
class PatientTimelineService
{
    use QueryFragments;

    // demogSql() needs these declared even though this lookup applies no
    // dimension filters - it always resolves one specific record.
    private ?string $district = null;

    private ?string $facility = null;

    private ?string $gender = null;

    public function find(string $record): ?array
    {
        [$demogSql, $bind] = $this->demogSql();
        $demog = DB::selectOne("
            SELECT d.*,
                   CASE WHEN d.dob REGEXP '".self::$DATE_RE."' THEN TIMESTAMPDIFF(YEAR, d.dob, CURDATE()) END AS age
            FROM ({$demogSql}) d
            WHERE d.record = ?
        ", [...$bind, $record]);

        if ($demog === null) {
            return null;
        }

        $projects = DB::select('SELECT DISTINCT project_id FROM redcap_data6 WHERE record = ? ORDER BY project_id', [$record]);

        $enc = $this->encountersSql();
        $timelineRows = DB::select("
            SELECT * FROM ({$enc}) e WHERE e.record = ?
            ORDER BY e.visit_date, e.instrument, e.inst
        ", [$record]);

        // Unlike dated encounters, a lifetime flag has no single event to pin
        // one project to - the same client can have e.g. mh_access = 'Y' at
        // both an FCH and an OPD visit, so this keeps every (instrument,
        // project) pair rather than collapsing to one project per flag.
        // artib (OI/ART Initial Baseline) is dateless the same way, but it's
        // a program step (baseline), not a standalone service, so it's split
        // out into the timeline below rather than the lifetime-flags list.
        $flagRows = DB::select("
            SELECT DISTINCT SUBSTRING_INDEX(field_name, '_', 1) AS instrument, project_id
            FROM redcap_data6
            WHERE project_id IN (".self::$P_ALL.") AND record = ?
              AND field_name IN ('mh_access', 'he_access', 'couns_access', 'artib_access') AND value = 'Y'
        ", [$record]);

        $timeline = array_map(fn ($r) => [
            'instrument' => $r->instrument,
            'family' => $this->familyLabel($r->instrument) ?? $r->instrument,
            'role' => $this->roleFor($r->instrument),
            'subject' => $this->subjectFor($r->instrument),
            'date' => $r->visit_date,
            'instance' => (int) $r->inst,
            'project_id' => (int) $r->project_id,
        ], $timelineRows);

        $flagProjects = [];
        foreach ($flagRows as $r) {
            $flagProjects[$r->instrument][] = (int) $r->project_id;
        }
        foreach ($flagProjects as $instrument => $flagProjectIds) {
            if ($instrument !== 'artib') {
                continue;
            }
            foreach ($flagProjectIds as $projectId) {
                $timeline[] = [
                    'instrument' => 'artib',
                    'family' => 'OI/ART',
                    'role' => 'baseline',
                    'subject' => null,
                    'date' => null,
                    'instance' => 1,
                    'project_id' => $projectId,
                ];
            }
        }
        unset($flagProjects['artib']);
        $lifetimeFlags = array_map(fn ($instrument, $projects) => [
            'instrument' => $instrument,
            'family' => $this->familyLabel($instrument) ?? $instrument,
            'projects' => $projects,
        ], array_keys($flagProjects), array_values($flagProjects));

        $dates = array_filter(array_column($timeline, 'date'));
        $familiesTouched = count(array_unique([
            ...array_column($timeline, 'family'),
            ...array_column($lifetimeFlags, 'family'),
        ]));

        return [
            'record' => $record,
            'demographics' => [
                'facility' => $demog->facility !== '' ? $demog->facility : null,
                'district' => $demog->district !== '' ? $demog->district : null,
                'sex' => match ($demog->gender) { '1' => 'Male', '2' => 'Female', default => 'Unknown' },
                'age' => $demog->age !== null ? (int) $demog->age : null,
                'age_band' => $this->ageBand($demog->age !== null ? (int) $demog->age : null),
            ],
            'projects' => array_map(fn ($p) => (int) $p->project_id, $projects),
            'timeline' => $timeline,
            'lifetime_flags' => $lifetimeFlags,
            'summary' => [
                'first_seen' => $dates ? min($dates) : null,
                'last_seen' => $dates ? max($dates) : null,
                'families_touched' => $familiesTouched,
                'total_encounters' => count($timeline),
            ],
        ];
    }

    private function ageBand(?int $age): ?string
    {
        return match (true) {
            $age === null => null,
            $age < 10 => 'Under 10',
            $age <= 14 => '10-14',
            $age <= 19 => '15-19',
            default => '20+',
        };
    }
}
