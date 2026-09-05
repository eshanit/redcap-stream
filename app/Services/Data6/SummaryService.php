<?php

namespace App\Services\Data6;

use Illuminate\Support\Facades\DB;

/**
 * Descriptive overview of the data6 population (projects 76/78/79):
 * demographic splits, service utilisation, first-seen trend, data quality.
 * Counts are unique records (the cross-project patient key), so mirrored
 * project copies never inflate a number.
 */
class SummaryService
{
    private const P_ALL = '76, 78, 79';

    private const DATE_RE = "^[0-9]{4}-[0-9]{2}-[0-9]{2}$";

    private const GENDER = ['1' => 'Male', '2' => 'Female'];

    private const PROFILE = [
        '1' => 'General population', '2' => 'Sex worker', '3' => 'MSM', '4' => 'WSW',
        '5' => 'PWUD', '6' => 'PWID', '7' => 'Transgender', '8' => 'Other',
    ];

    private const EDUCATION = [
        '0' => 'No education', '1' => 'Creche/ECD', '2' => 'Primary', '3' => 'Secondary',
        '4' => 'A level', '5' => 'College', '6' => 'Degree', '7' => 'Post graduate', '8' => 'Not provided',
    ];

    private const MARITAL = [
        '1' => 'Married', '2' => 'Never married', '3' => 'Widowed', '4' => 'Divorced/Separated',
        '5' => 'Living together', '6' => 'Minor', '7' => 'Not given', '8' => 'Divorced',
    ];

    private const SERVICES = [
        'sti_access' => 'STI register',
        'fp_access' => 'Family planning',
        'ancr_access' => 'ANC registration',
        'anc_access' => 'ANC follow-up',
        'pncr_access' => 'Mother-baby registration',
        'pncm_access' => 'PNC mother follow-up',
        'pncb_access' => 'PNC baby follow-up',
        'prepr_access' => 'PrEP registration',
        'prep_access' => 'PrEP follow-up',
        'artr_access' => 'OI/ART registration',
        'art_access' => 'OI/ART follow-up',
        'hts_access' => 'HIV testing',
        'mh_access' => 'Mental health',
        'he_access' => 'Health education',
        'couns_access' => 'Counselling',
        'pls_access' => 'Peer support',
        'opd_service' => 'Outpatient',
    ];

    public function compute(): array
    {
        $demog = $this->demogSql();

        return [
            'headline' => $this->headline($demog),
            'by_facility' => $this->groupedCount($demog, 'facility'),
            'by_district' => $this->groupedCount($demog, 'district'),
            'age_bands' => $this->ageBands($demog),
            'by_profile' => $this->decodedCount($demog, 'profile', self::PROFILE),
            'by_education' => $this->decodedCount($demog, 'education', self::EDUCATION),
            'by_marital' => $this->decodedCount($demog, 'marital', self::MARITAL),
            'service_utilisation' => $this->serviceUtilisation(),
            'first_seen_trend' => $this->firstSeenTrend(),
            'data_quality' => $this->dataQuality($demog),
        ];
    }

    private function demogSql(): string
    {
        return "SELECT record,
                       MAX(CASE WHEN field_name = 'demog_dateofbirth' THEN value END) AS dob,
                       MAX(CASE WHEN field_name = 'demog_gender' THEN value END) AS gender,
                       MAX(CASE WHEN field_name = 'demog_district' THEN value END) AS district,
                       MAX(CASE WHEN field_name = 'demog_facility' THEN value END) AS facility,
                       MAX(CASE WHEN field_name = 'demog_client_profile' THEN value END) AS profile,
                       MAX(CASE WHEN field_name = 'demog_education' THEN value END) AS education,
                       MAX(CASE WHEN field_name = 'demog_marital_status' THEN value END) AS marital,
                       MAX(CASE WHEN field_name = 'demog_have_contact' THEN value END) AS have_contact
                FROM redcap_data6
                WHERE project_id IN (".self::P_ALL.")
                  AND field_name IN ('demog_dateofbirth', 'demog_gender', 'demog_district', 'demog_facility',
                                     'demog_client_profile', 'demog_education', 'demog_marital_status', 'demog_have_contact')
                GROUP BY record";
    }

    private function headline(string $demog): array
    {
        $row = DB::selectOne("
            WITH demog AS ({$demog})
            SELECT COUNT(*) AS total,
                   COUNT(DISTINCT facility) AS facilities,
                   COUNT(DISTINCT district) AS districts,
                   SUM(gender = '2') AS female,
                   SUM(gender = '1') AS male,
                   SUM(dob REGEXP '".self::DATE_RE."' AND TIMESTAMPDIFF(YEAR, dob, CURDATE()) BETWEEN 10 AND 19) AS adolescents,
                   SUM(have_contact = 'Y') AS with_contact
            FROM demog
        ");

        $range = DB::selectOne("
            SELECT MIN(value) AS first_date, MAX(value) AS last_date
            FROM redcap_data6
            WHERE project_id IN (".self::P_ALL.")
              AND field_name IN ('sti_visit_date','fp_date','ancr_date','anc_date','pncr_date','pncm_visit_date',
                                 'pncb_visit_date','prepr_date','prep_visit_date','artr_registration_date',
                                 'art_review_date','hts_hiv_date','pls_date','opd_date')
              AND value REGEXP '".self::DATE_RE."'
              AND value BETWEEN '2010-01-01' AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
        ");

        return [
            'total' => (int) $row->total,
            'facilities' => (int) $row->facilities,
            'districts' => (int) $row->districts,
            'female' => (int) $row->female,
            'male' => (int) $row->male,
            'adolescents' => (int) $row->adolescents,
            'with_contact' => (int) $row->with_contact,
            'first_date' => $range->first_date,
            'last_date' => $range->last_date,
        ];
    }

    private function groupedCount(string $demog, string $column): array
    {
        $rows = DB::select("
            WITH demog AS ({$demog})
            SELECT COALESCE(NULLIF({$column}, ''), 'Unknown') AS label, COUNT(*) AS n
            FROM demog GROUP BY label ORDER BY n DESC
        ");

        return array_map(fn ($r) => ['label' => $r->label, 'count' => (int) $r->n], $rows);
    }

    private function decodedCount(string $demog, string $column, array $map): array
    {
        $rows = $this->groupedCount($demog, $column);

        return array_map(fn ($r) => [
            'label' => $map[$r['label']] ?? ($r['label'] === 'Unknown' ? 'Not recorded' : $r['label']),
            'count' => $r['count'],
        ], $rows);
    }

    private function ageBands(string $demog): array
    {
        $rows = DB::select("
            WITH demog AS ({$demog})
            SELECT CASE
                     WHEN dob IS NULL OR dob NOT REGEXP '".self::DATE_RE."' THEN 'Unknown'
                     WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 5 THEN 'Under 5'
                     WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 10 THEN '5-9'
                     WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 15 THEN '10-14'
                     WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 20 THEN '15-19'
                     WHEN TIMESTAMPDIFF(YEAR, dob, CURDATE()) < 25 THEN '20-24'
                     ELSE '25+'
                   END AS band,
                   COUNT(*) AS n
            FROM demog GROUP BY band
        ");

        $order = ['Under 5', '5-9', '10-14', '15-19', '20-24', '25+', 'Unknown'];
        $byBand = [];
        foreach ($rows as $r) {
            $byBand[$r->band] = (int) $r->n;
        }

        $out = [];
        foreach ($order as $band) {
            if (isset($byBand[$band])) {
                $out[] = ['label' => $band, 'count' => $byBand[$band]];
            }
        }

        return $out;
    }

    private function serviceUtilisation(): array
    {
        $fieldList = "'".implode("', '", array_keys(self::SERVICES))."'";
        $rows = DB::select("
            SELECT field_name, COUNT(DISTINCT record) AS n
            FROM redcap_data6
            WHERE project_id IN (".self::P_ALL.") AND field_name IN ({$fieldList}) AND value = 'Y'
            GROUP BY field_name ORDER BY n DESC
        ");

        return array_map(fn ($r) => [
            'label' => self::SERVICES[$r->field_name] ?? $r->field_name,
            'count' => (int) $r->n,
        ], $rows);
    }

    private function firstSeenTrend(): array
    {
        $rows = DB::select("
            WITH dates AS (
                SELECT record, MIN(value) AS first_seen
                FROM redcap_data6
                WHERE project_id IN (".self::P_ALL.")
                  AND field_name IN ('sti_visit_date','fp_date','ancr_date','anc_date','pncr_date','pncm_visit_date',
                                     'pncb_visit_date','prepr_date','prep_visit_date','artr_registration_date',
                                     'art_review_date','hts_hiv_date','pls_date','opd_date')
                  AND value REGEXP '".self::DATE_RE."'
                  AND value BETWEEN '2024-01-01' AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)
                GROUP BY record
            )
            SELECT DATE_FORMAT(first_seen, '%Y-%m') AS month, COUNT(*) AS n
            FROM dates GROUP BY month ORDER BY month
        ");

        return array_map(fn ($r) => ['month' => $r->month, 'count' => (int) $r->n], $rows);
    }

    private function dataQuality(string $demog): array
    {
        $row = DB::selectOne("
            WITH demog AS ({$demog})
            SELECT
              SUM(dob IS NULL OR dob = '') AS missing_dob,
              SUM(dob IS NOT NULL AND dob <> '' AND dob NOT REGEXP '".self::DATE_RE."') AS invalid_dob,
              SUM(gender IS NULL OR gender = '') AS missing_gender,
              SUM(facility IS NULL OR facility = '') AS missing_facility,
              SUM(district IS NULL OR district = '') AS missing_district,
              SUM(dob REGEXP '".self::DATE_RE."' AND dob > CURDATE()) AS future_dob
            FROM demog
        ");

        return [
            ['label' => 'Missing date of birth', 'count' => (int) $row->missing_dob],
            ['label' => 'Invalid date of birth', 'count' => (int) $row->invalid_dob],
            ['label' => 'Date of birth in the future', 'count' => (int) $row->future_dob],
            ['label' => 'Missing gender', 'count' => (int) $row->missing_gender],
            ['label' => 'Missing facility', 'count' => (int) $row->missing_facility],
            ['label' => 'Missing district', 'count' => (int) $row->missing_district],
        ];
    }
}
