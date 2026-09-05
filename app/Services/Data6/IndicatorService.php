<?php

namespace App\Services\Data6;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Computes the 45 AHP indicators against the long-format redcap_data6 table,
 * per the revised matrix (data/20260905_AHP_ Indicators_kpq.xlsx).
 *
 * Definitions: adolescent = 10-19 at the service date; LTFU = next
 * appointment missed by 28 days; age bands 10-14 / 15-19.
 */
class IndicatorService
{
    use QueryFragments;

    private string $from;

    private string $to;

    private ?string $district;

    private ?string $facility;

    private ?string $gender;

    private ?int $ageLo;

    private ?int $ageHi;

    public function compute(array $filters): array
    {
        $this->from = $this->assertDate($filters['from']);
        $this->to = $this->assertDate($filters['to']);
        $this->district = $filters['district'] ?? null;
        $this->facility = $filters['facility'] ?? null;
        $this->gender = $filters['gender'] ?? null;
        [$this->ageLo, $this->ageHi] = match ($filters['age_band'] ?? '10_19') {
            '10_14' => [10, 14],
            '15_19' => [15, 19],
            'all' => [null, null],
            default => [10, 19],
        };

        return [
            'values' => array_merge(
                $this->accessIndicators(),
                $this->hivTestingIndicators(),
                $this->artIndicators(),
                $this->mnchIndicators(),
                $this->fpPrepIndicators(),
                $this->mentalHealthIndicators(),
                $this->stiIndicators(),
                $this->peerIndicators(),
            ),
            'trend' => $this->monthlyTrend(),
            'facility_breakdown' => $this->facilityBreakdown(),
        ];
    }

    public function filterOptions(): array
    {
        $rows = DB::select(
            "SELECT field_name, value, COUNT(DISTINCT record) AS n
             FROM redcap_data6
             WHERE project_id IN (".self::$P_ALL.")
               AND field_name IN ('demog_district', 'demog_facility')
             GROUP BY field_name, value ORDER BY value"
        );

        $options = ['districts' => [], 'facilities' => []];
        foreach ($rows as $row) {
            if ($row->value === null || trim($row->value) === '') {
                continue;
            }
            $bucket = $row->field_name === 'demog_district' ? 'districts' : 'facilities';
            $options[$bucket][] = $row->value;
        }

        return $options;
    }

    private function one(string $sql, array $bindings): object
    {
        return DB::selectOne($sql, $bindings);
    }

    // ------------------------------------------------------------------
    // AHP001-003: service access
    // ------------------------------------------------------------------

    private function accessIndicators(): array
    {
        [$demog, $bind] = $this->demogSql();
        $enc = $this->encountersSql();
        $age = $this->ageCond('e.visit_date');
        $period = $this->periodCond('e.visit_date');

        $row = $this->one("
            WITH demog AS ({$demog}), enc AS ({$enc}),
            firsts AS (SELECT record, MIN(visit_date) AS first_visit FROM enc GROUP BY record)
            SELECT
              COUNT(DISTINCT CASE WHEN {$period} AND {$age} THEN e.record END) AS clients,
              COUNT(DISTINCT CASE WHEN {$period} AND {$age} AND e.visit_date = f.first_visit THEN e.record END) AS first_time,
              COUNT(CASE WHEN {$period} AND {$age} AND e.visit_date > f.first_visit THEN 1 END) AS repeat_visits
            FROM enc e
            JOIN demog d ON d.record = e.record
            JOIN firsts f ON f.record = e.record
        ", $bind);

        return [
            'access_clients' => ['value' => (int) $row->clients],
            'access_first' => ['value' => (int) $row->first_time],
            'access_repeat' => ['value' => (int) $row->repeat_visits],
        ];
    }

    // ------------------------------------------------------------------
    // AHP004-006: HIV testing - HTS register ONLY (kpq matrix)
    // ------------------------------------------------------------------

    private function hivTestingIndicators(): array
    {
        [$demog, $bind] = $this->demogSql();
        $hts = $this->pivotSql(self::$P_ALL, [
            'tested' => 'hts_tested', 'test_date' => 'hts_hiv_date', 'result' => 'hts_hiv_result',
        ]);
        $sti = $this->pivotSql(self::$P_ALL, ['tested' => 'sti_hiv_test', 'test_date' => 'sti_visit_date', 'result' => 'sti_hiv_test_result']);
        $prep = $this->pivotSql(self::$P_ALL, ['tested' => 'prep_hiv_test', 'test_date' => 'prep_visit_date', 'result' => 'prep_hiv_test_results']);
        $anc = $this->pivotSql(self::$P_FCH, ['result' => 'anc_hiv_test_results', 'test_date' => 'anc_date']);
        $artrTest = $this->pivotSql(self::$P_ART, ['first_test' => 'artr_first_hiv_test']);

        // Official AHP004-006 count the HTS register only; the "_all"
        // supplementary set unions every documented testing entry point.
        // artr_first_hiv_test is the confirmed test that led to enrolment
        // in HIV care, so it counts as tested AND positive at that date.
        $row = $this->one("
            WITH demog AS ({$demog}), htsp AS ({$hts}), tests AS (
                SELECT record, test_date, result FROM htsp t WHERE t.tested = '1'
                UNION ALL
                SELECT record, test_date, result FROM ({$sti}) t WHERE t.tested = '1'
                UNION ALL
                SELECT record, test_date, result FROM ({$prep}) t WHERE t.tested = '1'
                UNION ALL
                SELECT record, test_date, result FROM ({$anc}) t WHERE t.result IN ('P', 'N')
                UNION ALL
                SELECT record, first_test AS test_date, 'P' AS result FROM ({$artrTest}) t
                WHERE t.first_test REGEXP '".self::$DATE_RE."'
            )
            SELECT
              (SELECT COUNT(DISTINCT t.record) FROM htsp t JOIN demog d ON d.record = t.record
                WHERE t.tested = '1' AND {$this->periodCond('t.test_date')} AND {$this->ageCond('t.test_date')}) AS tested,
              (SELECT COUNT(DISTINCT t.record) FROM htsp t JOIN demog d ON d.record = t.record
                WHERE t.tested = '1' AND t.result = 'P' AND {$this->periodCond('t.test_date')} AND {$this->ageCond('t.test_date')}) AS positive,
              (SELECT COUNT(DISTINCT t.record) FROM tests t JOIN demog d ON d.record = t.record
                WHERE {$this->periodCond('t.test_date')} AND {$this->ageCond('t.test_date')}) AS tested_all,
              (SELECT COUNT(DISTINCT t.record) FROM tests t JOIN demog d ON d.record = t.record
                WHERE t.result = 'P' AND {$this->periodCond('t.test_date')} AND {$this->ageCond('t.test_date')}) AS positive_all
        ", $bind);

        return [
            'hiv_tested' => ['value' => (int) $row->tested],
            'hiv_positive' => ['value' => (int) $row->positive],
            'hiv_positivity' => $this->rate((int) $row->positive, (int) $row->tested),
            'hiv_tested_all' => ['value' => (int) $row->tested_all],
            'hiv_positive_all' => ['value' => (int) $row->positive_all],
            'hiv_positivity_all' => $this->rate((int) $row->positive_all, (int) $row->tested_all),
        ];
    }

    // ------------------------------------------------------------------
    // AHP007-017: ART cascade
    // ------------------------------------------------------------------

    private function artIndicators(): array
    {
        [$demog, $bind] = $this->demogSql();
        $art = $this->pivotSql(self::$P_ART, [
            'visit_date' => 'art_review_date',
            'next_visit' => 'art_next_review_date',
            'outcome' => 'art_final_outcome',
            'vl_done' => 'art_viral_load',
            'vl_date' => 'art_vl_collect_date',
            'vl_detected' => 'art_vl_detected',
            'vl_result' => 'art_vl_result',
        ]);
        $hts = $this->pivotSql(self::$P_ALL, ['art_init' => 'hts_art_init', 'test_date' => 'hts_hiv_date']);
        $artr = $this->pivotSql(self::$P_ART, ['referred' => 'artr_referred', 'reg_date' => 'artr_registration_date']);

        $dateOk = "p.visit_date REGEXP '".self::$DATE_RE."'";
        $vlDate = "COALESCE(NULLIF(p.vl_date, ''), p.visit_date)";
        $vlNumeric = "(p.vl_result REGEXP '".self::$NUM_RE."')";

        $flow = $this->one("
            WITH demog AS ({$demog}), artp AS ({$art})
            SELECT
              COUNT(DISTINCT CASE WHEN p.outcome = '4' AND {$this->periodCond('p.visit_date')} AND {$this->ageCond('p.visit_date')} THEN p.record END) AS tout,
              COUNT(DISTINCT CASE WHEN p.outcome = '5' AND {$this->periodCond('p.visit_date')} AND {$this->ageCond('p.visit_date')} THEN p.record END) AS died,
              COUNT(DISTINCT CASE WHEN p.vl_done = '1' AND {$this->periodCond($vlDate)} AND {$this->ageCond($vlDate)} THEN p.record END) AS vl_tested,
              COUNT(DISTINCT CASE WHEN p.vl_done = '1' AND p.vl_detected = '0' AND {$this->periodCond($vlDate)} AND {$this->ageCond($vlDate)} THEN p.record END) AS tnd,
              COUNT(DISTINCT CASE WHEN p.vl_done = '1' AND (p.vl_detected = '0' OR ({$vlNumeric} AND CAST(p.vl_result AS UNSIGNED) < 1000)) AND {$this->periodCond($vlDate)} AND {$this->ageCond($vlDate)} THEN p.record END) AS suppressed,
              COUNT(DISTINCT CASE WHEN {$vlNumeric} AND CAST(p.vl_result AS UNSIGNED) >= 1000 AND {$this->periodCond($vlDate)} AND {$this->ageCond($vlDate)} THEN p.record END) AS vl_high
            FROM artp p
            JOIN demog d ON d.record = p.record
        ", $bind);

        // AHP007: initiation per the HTS register (hts_art_init = Y).
        $init = $this->one("
            WITH demog AS ({$demog}), htsp AS ({$hts}),
            inits AS (
                SELECT record, MIN(test_date) AS init_dt
                FROM htsp WHERE art_init = 'Y' AND test_date REGEXP '".self::$DATE_RE."'
                GROUP BY record
            )
            SELECT COUNT(*) AS initiated
            FROM inits i JOIN demog d ON d.record = i.record
            WHERE {$this->periodCond('i.init_dt')} AND {$this->ageCond('i.init_dt')}
        ", $bind);

        // AHP011: transfer-in per the kpq matrix = artr_referred.
        $ti = $this->one("
            WITH demog AS ({$demog}), artrp AS ({$artr})
            SELECT COUNT(DISTINCT p.record) AS ti
            FROM artrp p JOIN demog d ON d.record = p.record
            WHERE p.referred = '1' AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')}
        ", $bind);

        $status = $this->one("
            WITH demog AS ({$demog}), artp AS ({$art}),
            latest AS (
                SELECT p.*, ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date DESC) AS rn
                FROM artp p WHERE {$dateOk} AND p.visit_date <= '{$this->to}'
            )
            SELECT
              COUNT(CASE WHEN (l.outcome IS NULL OR l.outcome NOT IN ('3','4','5','6'))
                          AND l.next_visit REGEXP '".self::$DATE_RE."'
                          AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) >= '{$this->to}' THEN 1 END) AS tx_curr,
              COUNT(CASE WHEN (l.outcome IS NULL OR l.outcome NOT IN ('4','5'))
                          AND l.next_visit REGEXP '".self::$DATE_RE."'
                          AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) < '{$this->to}' THEN 1 END) AS ltfu
            FROM latest l
            JOIN demog d ON d.record = l.record
            WHERE l.rn = 1 AND {$this->ageAtPeriodEnd()}
        ", $bind);

        // AHP009: cohort = AHP007 initiations 12 months earlier; retained via ART visit history.
        $cohortFrom = date('Y-m-d', strtotime($this->from.' -12 months'));
        $cohortTo = date('Y-m-d', strtotime($this->to.' -12 months'));
        $retention = $this->one("
            WITH demog AS ({$demog}), artp AS ({$art}), htsp AS ({$hts}),
            inits AS (
                SELECT record, MIN(test_date) AS init_dt
                FROM htsp WHERE art_init = 'Y' AND test_date REGEXP '".self::$DATE_RE."'
                GROUP BY record
            )
            SELECT COUNT(*) AS cohort, COALESCE(SUM(t.retained), 0) AS retained
            FROM (
                SELECT i.record,
                       CASE WHEN EXISTS (
                           SELECT 1 FROM artp v
                           WHERE v.record = i.record AND v.visit_date REGEXP '".self::$DATE_RE."'
                             AND v.visit_date BETWEEN DATE_ADD(i.init_dt, INTERVAL 270 DAY) AND DATE_ADD(i.init_dt, INTERVAL 455 DAY)
                       ) AND NOT EXISTS (
                           SELECT 1 FROM artp o
                           WHERE o.record = i.record AND o.outcome IN ('4','5')
                             AND o.visit_date REGEXP '".self::$DATE_RE."'
                             AND o.visit_date <= DATE_ADD(i.init_dt, INTERVAL 365 DAY)
                       ) THEN 1 ELSE 0 END AS retained
                FROM inits i
                JOIN demog d ON d.record = i.record
                WHERE i.init_dt BETWEEN '{$cohortFrom}' AND '{$cohortTo}' AND {$this->ageCond('i.init_dt')}
            ) t
        ", $bind);

        return [
            'art_initiated' => ['value' => (int) $init->initiated],
            'art_current' => ['value' => (int) $status->tx_curr],
            'art_retention' => $this->rate((int) $retention->retained, (int) $retention->cohort),
            'art_ltfu' => ['value' => (int) $status->ltfu],
            'art_ti' => ['value' => (int) $ti->ti],
            'art_to' => ['value' => (int) $flow->tout],
            'art_died' => ['value' => (int) $flow->died],
            'art_vl_tested' => ['value' => (int) $flow->vl_tested],
            'art_vl_tnd' => ['value' => (int) $flow->tnd],
            'art_vl_suppressed' => $this->rate((int) $flow->suppressed, (int) $flow->vl_tested),
            'art_vl_high' => ['value' => (int) $flow->vl_high],
        ];
    }

    // ------------------------------------------------------------------
    // AHP018-028: ANC / delivery / PNC (interim PNCR proxies until the
    // Labour & Delivery form is live - config('data6_indicators.ld'))
    // ------------------------------------------------------------------

    private function mnchIndicators(): array
    {
        [$demog, $bind] = $this->demogSql();

        $ancr = $this->pivotSql(self::$P_FCH, [
            'reg_date' => 'ancr_date', 'first_booking' => 'ancr_first_booking',
            'hiv_prior' => 'ancr_hiv_prior', 'contact_no' => 'ancr_contact_number',
        ]);
        $anc = $this->pivotSql(self::$P_FCH, ['visit_date' => 'anc_date', 'contact_no' => 'anc_contact_number']);
        $pncr = $this->pivotSql(self::$P_FCH, [
            'reg_date' => 'pncr_date', 'place' => 'pncr_place_of_delivery',
            'hiv_post' => 'pncr_hiv_status_post', 'on_art' => 'pncr_hiv_status_art',
            'baby_dob' => 'pncr_date_of_birth',
        ]);
        $pncm = $this->pivotSql(self::$P_FCH, [
            'visit_date' => 'pncm_visit_date', 'follow_up' => 'pncm_mother_follow_up', 'hiv_tested' => 'pncm_hiv_tested',
        ]);
        $pncb = $this->pivotSql(self::$P_FCH, ['visit_date' => 'pncb_visit_date', 'infant_status' => 'pncb_infant_follow_ups']);

        $ancRow = $this->one("
            WITH demog AS ({$demog}), ancrp AS ({$ancr})
            SELECT
              COUNT(DISTINCT CASE WHEN p.first_booking = '1' AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN p.record END) AS new_bookings,
              COUNT(DISTINCT CASE WHEN p.first_booking = '1' AND p.hiv_prior IN ('0','1') AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN p.record END) AS first_tested
            FROM ancrp p JOIN demog d ON d.record = p.record
        ", $bind);

        $pncrRow = $this->one("
            WITH demog AS ({$demog}), pncrp AS ({$pncr}),
            contacts AS (
                SELECT record, MAX(CASE WHEN contact_no REGEXP '".self::$NUM_RE."' THEN CAST(contact_no AS UNSIGNED) ELSE 0 END) AS max_contact
                FROM (
                    SELECT record, contact_no FROM ({$ancr}) a
                    UNION ALL
                    SELECT record, contact_no FROM ({$anc}) b
                ) c GROUP BY record
            ),
            pnc72 AS (SELECT DISTINCT m.record FROM ({$pncm}) m
                      JOIN pncrp r ON r.record = m.record
                      WHERE m.visit_date REGEXP '".self::$DATE_RE."' AND r.baby_dob REGEXP '".self::$DATE_RE."'
                        AND DATEDIFF(m.visit_date, r.baby_dob) BETWEEN 0 AND 3)
            SELECT
              COUNT(CASE WHEN {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN 1 END) AS deliveries,
              COUNT(CASE WHEN p.place = '1' AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN 1 END) AS institutional,
              COUNT(CASE WHEN p.place IN ('2','3') AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN 1 END) AS non_institutional,
              COUNT(CASE WHEN p.place = '3' AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN 1 END) AS bba,
              COUNT(CASE WHEN {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} AND c.max_contact >= 8 THEN 1 END) AS anc8,
              COUNT(CASE WHEN {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} AND pn.record IS NOT NULL THEN 1 END) AS pnc_within_72,
              COUNT(CASE WHEN p.hiv_post = 'P' AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN 1 END) AS hiv_pos_deliveries,
              COUNT(CASE WHEN p.hiv_post = 'P' AND p.on_art = '1' AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN 1 END) AS on_art_delivery
            FROM pncrp p
            JOIN demog d ON d.record = p.record
            LEFT JOIN contacts c ON c.record = p.record
            LEFT JOIN pnc72 pn ON pn.record = p.record
        ", $bind);

        $deathRow = $this->one("
            WITH demog AS ({$demog}), pncmp AS ({$pncm}), pncbp AS ({$pncb}),
            babydob AS (SELECT record, MAX(baby_dob) AS baby_dob FROM ({$pncr}) r WHERE baby_dob REGEXP '".self::$DATE_RE."' GROUP BY record)
            SELECT
              (SELECT COUNT(DISTINCT m.record) FROM pncmp m JOIN demog d ON d.record = m.record
                WHERE m.follow_up = '5' AND {$this->periodCond('m.visit_date')}) AS maternal_deaths,
              (SELECT COUNT(*) FROM pncbp b
                 JOIN demog d ON d.record = b.record
                 JOIN babydob bd ON bd.record = b.record
                WHERE b.infant_status = '6' AND {$this->periodCond('b.visit_date')}
                  AND b.visit_date REGEXP '".self::$DATE_RE."'
                  AND DATEDIFF(b.visit_date, bd.baby_dob) BETWEEN 0 AND 7) AS neonatal_deaths
        ", $bind);

        $retest = $this->one("
            WITH demog AS ({$demog}), pncmp AS ({$pncm}),
            negmums AS (SELECT DISTINCT record FROM ({$pncr}) r WHERE r.hiv_post = 'N')
            SELECT
              COUNT(DISTINCT CASE WHEN {$this->periodCond('m.visit_date')} THEN m.record END) AS denom,
              COUNT(DISTINCT CASE WHEN {$this->periodCond('m.visit_date')} AND m.hiv_tested = '1' THEN m.record END) AS numer
            FROM pncmp m
            JOIN demog d ON d.record = m.record
            JOIN negmums n ON n.record = m.record
        ", $bind);

        return [
            'anc_new' => ['value' => (int) $ancRow->new_bookings],
            'anc_8plus' => $this->rate((int) $pncrRow->anc8, (int) $pncrRow->deliveries),
            'births_inst' => ['value' => (int) $pncrRow->institutional],
            'births_home' => ['value' => (int) $pncrRow->non_institutional, 'extra' => ['bba' => (int) $pncrRow->bba]],
            'stillbirths' => ['value' => null],
            'neonatal_deaths' => ['value' => (int) $deathRow->neonatal_deaths],
            'maternal_deaths' => ['value' => (int) $deathRow->maternal_deaths],
            'pnc_72h' => $this->rate((int) $pncrRow->pnc_within_72, (int) $pncrRow->deliveries),
            'anc_first_tested' => $this->rate((int) $ancRow->first_tested, (int) $ancRow->new_bookings),
            'bf_retest' => $this->rate((int) $retest->numer, (int) $retest->denom),
            'art_at_delivery' => $this->rate((int) $pncrRow->on_art_delivery, (int) $pncrRow->hiv_pos_deliveries),
        ];
    }

    // ------------------------------------------------------------------
    // AHP029-034: family planning and PrEP
    // ------------------------------------------------------------------

    private function fpPrepIndicators(): array
    {
        [$demog, $bind] = $this->demogSql();

        $fp = $this->pivotSql(self::$P_FCH, ['visit_date' => 'fp_date', 'category' => 'fp_client_category']);
        $prepr = $this->pivotSql(self::$P_ALL, [
            'reg_date' => 'prepr_date', 'screened' => 'prepr_screened', 'visit_status' => 'prepr_visit_status',
            'initiate' => 'prepr_prep_initiate', 'start_date' => 'prepr_prep_start_date',
        ]);

        $fpRow = $this->one("
            WITH demog AS ({$demog}), fpp AS ({$fp})
            SELECT
              COUNT(DISTINCT CASE WHEN p.category = 'N' AND {$this->periodCond('p.visit_date')} AND {$this->ageCond('p.visit_date')} THEN p.record END) AS new_users,
              COUNT(DISTINCT CASE WHEN p.category = 'R' AND {$this->periodCond('p.visit_date')} AND {$this->ageCond('p.visit_date')} THEN p.record END) AS repeat_users
            FROM fpp p JOIN demog d ON d.record = p.record
        ", $bind);

        // kpq matrix: initiation via visit_status N (or explicit initiate flag);
        // continuing = C + N; discontinued = D - all from the PrEP register.
        $initDate = "COALESCE(NULLIF(p.start_date, ''), p.reg_date)";
        $prepRow = $this->one("
            WITH demog AS ({$demog}), preprp AS ({$prepr})
            SELECT
              COUNT(DISTINCT CASE WHEN p.screened = '1' AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN p.record END) AS screened,
              COUNT(DISTINCT CASE WHEN (p.visit_status = 'N' OR p.initiate = '1') AND {$this->periodCond($initDate)} AND {$this->ageCond($initDate)} THEN p.record END) AS initiated,
              COUNT(DISTINCT CASE WHEN p.visit_status IN ('C', 'N') AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN p.record END) AS continuing,
              COUNT(DISTINCT CASE WHEN p.visit_status = 'D' AND {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')} THEN p.record END) AS discontinued
            FROM preprp p JOIN demog d ON d.record = p.record
        ", $bind);

        return [
            'fp_new' => ['value' => (int) $fpRow->new_users],
            'fp_repeat' => ['value' => (int) $fpRow->repeat_users],
            'prep_screened' => ['value' => (int) $prepRow->screened],
            'prep_initiated' => ['value' => (int) $prepRow->initiated],
            'prep_continuing' => ['value' => (int) $prepRow->continuing],
            'prep_discontinued' => ['value' => (int) $prepRow->discontinued],
        ];
    }

    // ------------------------------------------------------------------
    // AHP035-040: mental health & substance use (no date field: all-time)
    // ------------------------------------------------------------------

    private function mentalHealthIndicators(): array
    {
        [$demog, $bind] = $this->demogSql();
        $mh = $this->pivotSql(self::$P_ALL, [
            'screened' => 'mh_screening_tools', 'result' => 'mh_screening_results',
            'managed' => 'mh_management_outcome', 'substance' => 'mh_substance_identified',
        ]);

        $row = $this->one("
            WITH demog AS ({$demog}), mhp AS ({$mh})
            SELECT
              COUNT(DISTINCT CASE WHEN p.screened = '1' THEN p.record END) AS screened,
              COUNT(DISTINCT CASE WHEN p.result = 'P' THEN p.record END) AS positive,
              COUNT(DISTINCT CASE WHEN p.managed IN ('R','M','B') THEN p.record END) AS managed,
              COUNT(DISTINCT CASE WHEN p.screened = '1' AND p.substance IN ('0','1') THEN p.record END) AS su_screened,
              COUNT(DISTINCT CASE WHEN p.substance = '1' THEN p.record END) AS su_positive,
              COUNT(DISTINCT CASE WHEN p.substance = '1' AND p.managed IN ('R','M','B') THEN p.record END) AS su_managed
            FROM mhp p
            JOIN demog d ON d.record = p.record
            WHERE {$this->ageAtPeriodEnd()}
        ", $bind);

        return [
            'mh_screened' => ['value' => (int) $row->screened],
            'mh_positive' => ['value' => (int) $row->positive],
            'mh_managed' => ['value' => (int) $row->managed],
            'su_screened' => ['value' => (int) $row->su_screened],
            'su_positive' => ['value' => (int) $row->su_positive],
            'su_managed' => ['value' => (int) $row->su_managed],
        ];
    }

    // ------------------------------------------------------------------
    // AHP041-042: STI
    // ------------------------------------------------------------------

    private function stiIndicators(): array
    {
        [$demog, $bind] = $this->demogSql();
        $sti = $this->pivotSql(self::$P_ALL, [
            'visit_date' => 'sti_visit_date', 'alt_date' => 'sti_date', 'treated' => 'sti_patient_treated',
        ]);
        $dt = "COALESCE(NULLIF(p.visit_date, ''), p.alt_date)";

        $row = $this->one("
            WITH demog AS ({$demog}), stip AS ({$sti})
            SELECT
              COUNT(DISTINCT CASE WHEN {$this->periodCond($dt)} AND {$this->ageCond($dt)} THEN p.record END) AS screened,
              COUNT(DISTINCT CASE WHEN p.treated = '1' AND {$this->periodCond($dt)} AND {$this->ageCond($dt)} THEN p.record END) AS treated
            FROM stip p JOIN demog d ON d.record = p.record
        ", $bind);

        return [
            'sti_screened' => ['value' => (int) $row->screened],
            'sti_treated' => ['value' => (int) $row->treated],
        ];
    }

    // ------------------------------------------------------------------
    // AHP043-045: peer support (sums, deduplicated across mirrors)
    // ------------------------------------------------------------------

    private function peerIndicators(): array
    {
        [$demog, $bind] = $this->demogSql();
        $pls = $this->pivotSql(self::$P_ALL, [
            'session_date' => 'pls_date', 'conducted' => 'pls_session_conducted',
            'sessions' => 'pls_number', 'reached' => 'pls_ado_number', 'support' => 'pls_support_conducted',
        ]);

        $row = $this->one("
            WITH demog AS ({$demog}),
            plsp AS (SELECT DISTINCT record, session_date, inst, conducted, sessions, reached, support FROM ({$pls}) raw)
            SELECT
              SUM(CASE WHEN p.conducted = '1' AND {$this->periodCond('p.session_date')} AND p.sessions REGEXP '".self::$NUM_RE."' THEN CAST(p.sessions AS UNSIGNED) ELSE 0 END) AS sessions,
              SUM(CASE WHEN p.conducted = '1' AND {$this->periodCond('p.session_date')} AND p.reached REGEXP '".self::$NUM_RE."' THEN CAST(p.reached AS UNSIGNED) ELSE 0 END) AS reached,
              COUNT(CASE WHEN p.support = '1' AND {$this->periodCond('p.session_date')} THEN 1 END) AS support_groups
            FROM plsp p
            JOIN demog d ON d.record = p.record
        ", $bind);

        return [
            'peer_sessions' => ['value' => (int) ($row->sessions ?? 0)],
            'peer_reached' => ['value' => (int) ($row->reached ?? 0)],
            'support_groups' => ['value' => (int) $row->support_groups],
        ];
    }

    // ------------------------------------------------------------------
    // Overview extras
    // ------------------------------------------------------------------

    private function monthlyTrend(): array
    {
        [$demog, $bind] = $this->demogSql();
        $enc = $this->encountersSql();

        $rows = DB::select("
            WITH demog AS ({$demog}), enc AS ({$enc})
            SELECT DATE_FORMAT(e.visit_date, '%Y-%m') AS month,
                   COUNT(DISTINCT e.record) AS clients,
                   COUNT(*) AS visits
            FROM enc e
            JOIN demog d ON d.record = e.record
            WHERE {$this->periodCond('e.visit_date')} AND {$this->ageCond('e.visit_date')}
            GROUP BY DATE_FORMAT(e.visit_date, '%Y-%m')
            ORDER BY month
        ", $bind);

        return array_map(fn ($r) => ['month' => $r->month, 'clients' => (int) $r->clients, 'visits' => (int) $r->visits], $rows);
    }

    private function facilityBreakdown(): array
    {
        [$demog, $bind] = $this->demogSql();
        $enc = $this->encountersSql();

        $rows = DB::select("
            WITH demog AS ({$demog}), enc AS ({$enc})
            SELECT COALESCE(d.facility, 'Unknown') AS facility,
                   COUNT(DISTINCT e.record) AS clients
            FROM enc e
            JOIN demog d ON d.record = e.record
            WHERE {$this->periodCond('e.visit_date')} AND {$this->ageCond('e.visit_date')}
            GROUP BY d.facility
            ORDER BY clients DESC
        ", $bind);

        return array_map(fn ($r) => ['facility' => $r->facility, 'clients' => (int) $r->clients], $rows);
    }

    private function rate(int $numerator, int $denominator): array
    {
        return [
            'value' => $denominator > 0 ? round($numerator / $denominator * 100, 1) : null,
            'numerator' => $numerator,
            'denominator' => $denominator,
        ];
    }

    private function assertDate(string $date): string
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new InvalidArgumentException('Invalid date filter.');
        }

        return $date;
    }
}
