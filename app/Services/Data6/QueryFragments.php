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

    /** Pivot one instrument's fields to columns, one row per (record, event, instance). */
    protected function pivotSql(string $projects, array $fields): string
    {
        $cases = [];
        foreach ($fields as $alias => $field) {
            $cases[] = "MAX(CASE WHEN field_name = '{$field}' THEN value END) AS {$alias}";
        }
        $fieldList = "'".implode("', '", array_values($fields))."'";

        return 'SELECT record, event_id, COALESCE(instance, 1) AS inst, '.implode(', ', $cases).'
                FROM redcap_data6
                WHERE project_id IN ('.$projects.")
                  AND field_name IN ({$fieldList})
                GROUP BY record, event_id, COALESCE(instance, 1)";
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

    /**
     * UNION of one row per (record, instrument, date, instance) for every
     * dated encounter, deduplicated across mirrored projects.
     */
    protected function encountersSql(): string
    {
        $sources = [
            ['sti', self::$P_ALL, 'sti_visit_date'],
            ['fp', self::$P_FCH, 'fp_date'],
            ['ancr', self::$P_FCH, 'ancr_date'],
            ['anc', self::$P_FCH, 'anc_date'],
            ['pncr', self::$P_FCH, 'pncr_date'],
            ['pncm', self::$P_FCH, 'pncm_visit_date'],
            ['pncb', self::$P_FCH, 'pncb_visit_date'],
            ['prepr', self::$P_ALL, 'prepr_date'],
            ['prep', self::$P_ALL, 'prep_visit_date'],
            ['artr', self::$P_ART, 'artr_registration_date'],
            ['art', self::$P_ART, 'art_review_date'],
            ['hts', self::$P_ALL, 'hts_hiv_date'],
            ['pls', self::$P_ALL, 'pls_date'],
            ['opd', self::$P_OPD, 'opd_date'],
        ];

        $parts = [];
        foreach ($sources as [$name, $projects, $dateField]) {
            $parts[] = "SELECT DISTINCT record, '{$name}' AS instrument, value AS visit_date, COALESCE(instance, 1) AS inst
                        FROM redcap_data6
                        WHERE project_id IN ({$projects}) AND field_name = '{$dateField}'
                          AND value REGEXP '".self::$DATE_RE."'";
        }

        return implode("\nUNION ALL\n", $parts);
    }
}
