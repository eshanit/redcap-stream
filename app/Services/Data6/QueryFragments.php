<?php

namespace App\Services\Data6;

/**
 * Shared SQL building blocks for the data6 indicator engine and report
 * builder. The host class must define these properties before calling:
 *   string $from, string $to               - reporting period (Y-m-d, validated)
 *   ?string $district, $facility, $gender  - optional demog filters
 *   ?int $ageLo, ?int $ageHi               - age band (null = all ages)
 *
 * Dedup rules (docs/plan_ahp_dashboard.md §1, confirmed 2026-09-03):
 * service-specific instruments read only their home project; shared
 * instruments union across projects and dedupe by record/date/instance.
 */
trait QueryFragments
{
    protected static string $P_ALL = '76, 78, 79';

    protected static string $P_FCH = '76';

    protected static string $P_ART = '78';

    protected static string $P_OPD = '79';

    protected static string $DATE_RE = '^[0-9]{4}-[0-9]{2}-[0-9]{2}$';

    protected static string $NUM_RE = '^[0-9]+$';

    /** Demographics dimension, pre-filtered by district/facility/gender. */
    protected function demogSql(): array
    {
        $having = [];
        $bindings = [];
        foreach (['district' => $this->district, 'facility' => $this->facility, 'gender' => $this->gender] as $col => $val) {
            if ($val !== null && $val !== '') {
                $having[] = "{$col} = ?";
                $bindings[] = $val;
            }
        }
        $havingSql = $having === [] ? '' : 'HAVING '.implode(' AND ', $having);

        $sql = "SELECT record,
                       MAX(CASE WHEN field_name = 'demog_dateofbirth' THEN value END) AS dob,
                       MAX(CASE WHEN field_name = 'demog_gender' THEN value END) AS gender,
                       MAX(CASE WHEN field_name = 'demog_district' THEN value END) AS district,
                       MAX(CASE WHEN field_name = 'demog_facility' THEN value END) AS facility
                FROM redcap_data6
                WHERE project_id IN (".self::$P_ALL.")
                  AND field_name IN ('demog_dateofbirth', 'demog_gender', 'demog_district', 'demog_facility')
                GROUP BY record {$havingSql}";

        return [$sql, $bindings];
    }

    /**
     * Pivot one instrument's fields to columns, one row per (record, event,
     * instance). Safe across more than one project: `event_id` is a
     * project-LOCAL form identifier - confirmed by its ranges never
     * overlapping between projects 76/78/79 - so it can never associate "the
     * same submission" across two projects, only isolate rows within one.
     * When $projects names more than one project, each is therefore pivoted
     * separately (event_id stays meaningful there), the results are unioned,
     * and an outer SELECT DISTINCT collapses true mirrors - rows a client's
     * form produced identically in more than one project - without needing
     * to know which pivoted column is a date. A single project (the common
     * case) skips the union/distinct entirely: zero behaviour change.
     *
     * This same mechanism is what makes it safe for any instrument to be
     * unioned across whichever of 76/78/79 its fields actually appear in,
     * rather than assuming a fixed "home" project per instrument.
     */
    protected function pivotSql(string $projects, array $fields): string
    {
        $projectIds = array_values(array_filter(array_map('trim', explode(',', $projects)), fn ($p) => $p !== ''));
        $cases = [];
        foreach ($fields as $alias => $field) {
            $cases[] = "MAX(CASE WHEN field_name = '{$field}' THEN value END) AS {$alias}";
        }
        $fieldList = "'".implode("', '", array_values($fields))."'";

        $perProject = array_map(fn ($p) => "SELECT record, event_id, COALESCE(instance, 1) AS inst, ".implode(', ', $cases).'
                FROM redcap_data6
                WHERE project_id IN ('.$p.")
                  AND field_name IN ({$fieldList})
                GROUP BY record, event_id, COALESCE(instance, 1)", $projectIds);

        if ($perProject === []) {
            // Defensive only - no call site passes an empty project list today.
            $nulls = implode(', ', array_map(fn ($a) => "NULL AS {$a}", array_keys($fields)));

            return "SELECT NULL AS record, NULL AS inst, {$nulls} FROM redcap_data6 WHERE 1 = 0";
        }
        if (count($perProject) === 1) {
            return $perProject[0];
        }

        $columns = 'record, inst, '.implode(', ', array_keys($fields));

        return "SELECT DISTINCT {$columns} FROM (".implode("\nUNION ALL\n", $perProject).') u';
    }

    /** Age-at-date condition against the demog alias `d`. */
    protected function ageCond(string $dateExpr): string
    {
        if ($this->ageLo === null) {
            return '1 = 1';
        }

        return "(d.dob REGEXP '".self::$DATE_RE."' AND {$dateExpr} REGEXP '".self::$DATE_RE."'
                 AND TIMESTAMPDIFF(YEAR, d.dob, {$dateExpr}) BETWEEN {$this->ageLo} AND {$this->ageHi})";
    }

    /** Age condition evaluated at the period end (status/no-date indicators). */
    protected function ageAtPeriodEnd(): string
    {
        if ($this->ageLo === null) {
            return '1 = 1';
        }

        return "(d.dob REGEXP '".self::$DATE_RE."'
                 AND TIMESTAMPDIFF(YEAR, d.dob, '{$this->to}') BETWEEN {$this->ageLo} AND {$this->ageHi})";
    }

    protected function periodCond(string $dateExpr): string
    {
        return "({$dateExpr} REGEXP '".self::$DATE_RE."' AND {$dateExpr} BETWEEN '{$this->from}' AND '{$this->to}')";
    }

    /** instrument prefix -> human service-family label, shared by every
     *  service that groups encounters into families (Insights, patient
     *  timeline). Dateless instruments (mh/he/couns/artib) are access flags
     *  only - artib (OI/ART Initial Baseline) has no single visit-date field,
     *  only per-lab-test dates, so it's tracked the same way. */
    protected static array $FAMILY = [
        'sti' => 'STI', 'fp' => 'Family planning', 'ancr' => 'ANC', 'anc' => 'ANC',
        'pncr' => 'PNC', 'pncm' => 'PNC', 'pncb' => 'PNC', 'prepr' => 'PrEP', 'prep' => 'PrEP',
        'artr' => 'OI/ART', 'artib' => 'OI/ART', 'art' => 'OI/ART', 'hts' => 'HIV testing', 'pls' => 'Peer support',
        'opd' => 'Outpatient', 'mh' => 'Mental health', 'he' => 'Health education', 'couns' => 'Counselling',
    ];

    /**
     * instrument -> its role within a program that has a formal
     * registration/follow-up structure (ANC, PNC, PrEP, OI/ART). Every
     * instrument not listed has no such structure - each occurrence just
     * stands on its own, tracked by its own visit date (STI, HTS, ...).
     */
    protected static array $ROLE = [
        'ancr' => 'registration', 'anc' => 'follow_up',
        'pncr' => 'registration', 'pncm' => 'follow_up', 'pncb' => 'follow_up',
        'prepr' => 'registration', 'prep' => 'follow_up',
        'artr' => 'registration', 'artib' => 'baseline', 'art' => 'follow_up',
    ];

    /** PNC's two follow-up instruments track different people under one
     *  registration - who a given entry is actually about. */
    protected static array $SUBJECT = ['pncm' => 'Mother', 'pncb' => 'Baby'];

    protected function familyLabel(string $instrument): ?string
    {
        return self::$FAMILY[$instrument] ?? null;
    }

    protected function roleFor(string $instrument): ?string
    {
        return self::$ROLE[$instrument] ?? null;
    }

    protected function subjectFor(string $instrument): ?string
    {
        return self::$SUBJECT[$instrument] ?? null;
    }

    /**
     * UNION of one row per (record, instrument, date, instance) for every
     * dated encounter, deduplicated across mirrored projects. `project_id`
     * is the lowest project the encounter appears under when an instrument
     * has mirrored or independently-entered copies in more than one project
     * - informational only (which form it can be reviewed in), never used
     * to multiply the encounter itself, since GROUP BY (not SELECT DISTINCT)
     * is what still collapses those mirrored copies to one row.
     *
     * Every instrument is scoped to P_ALL: none of them are reliably
     * confined to one "home" project in practice (confirmed empirically -
     * e.g. family planning, nominally FCH-only, has real client visits
     * recorded under OI/ART too), so every instrument is unioned across
     * whichever of 76/78/79 its fields actually appear in.
     */
    protected function encountersSql(): string
    {
        $sources = [
            ['sti', self::$P_ALL, 'sti_visit_date'],
            ['fp', self::$P_ALL, 'fp_date'],
            ['ancr', self::$P_ALL, 'ancr_date'],
            ['anc', self::$P_ALL, 'anc_date'],
            ['pncr', self::$P_ALL, 'pncr_date'],
            ['pncm', self::$P_ALL, 'pncm_visit_date'],
            ['pncb', self::$P_ALL, 'pncb_visit_date'],
            ['prepr', self::$P_ALL, 'prepr_date'],
            ['prep', self::$P_ALL, 'prep_visit_date'],
            ['artr', self::$P_ALL, 'artr_registration_date'],
            ['art', self::$P_ALL, 'art_review_date'],
            ['hts', self::$P_ALL, 'hts_hiv_date'],
            ['pls', self::$P_ALL, 'pls_date'],
            ['opd', self::$P_ALL, 'opd_date'],
        ];

        $parts = [];
        foreach ($sources as [$name, $projects, $dateField]) {
            $parts[] = "SELECT record, '{$name}' AS instrument, value AS visit_date, COALESCE(instance, 1) AS inst, MIN(project_id) AS project_id
                        FROM redcap_data6
                        WHERE project_id IN ({$projects}) AND field_name = '{$dateField}'
                          AND value REGEXP '".self::$DATE_RE."'
                        GROUP BY record, value, COALESCE(instance, 1)";
        }

        return implode("\nUNION ALL\n", $parts);
    }
}
