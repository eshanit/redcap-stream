<?php

namespace App\Services\Data6;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Disaggregated M&E report over the 45 AHP indicators: for one reporting
 * period, every indicator's total plus the splits the kpq matrix requires
 * (age band 10-14/15-19, sex, facility, district; service point for AHP001).
 *
 * Approach: each indicator has a detail rowset (record + reference date +
 * optional flag/value columns); one SQL query fetches the rows joined to
 * the demographics dimension, and the buckets are aggregated in PHP.
 * Adolescent filter (10-19 at the reference date) always applies.
 */
class ReportService
{
    use QueryFragments;

    private string $from;

    private string $to;

    private ?string $district = null;

    private ?string $facility = null;

    private ?string $gender = null;

    private ?int $ageLo = 10;

    private ?int $ageHi = 19;

    public function report(string $from, string $to): array
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            throw new InvalidArgumentException('Invalid report period.');
        }
        $this->from = $from;
        $this->to = $to;

        $out = [];
        foreach (config('data6_indicators.indicators') as $meta) {
            $out[] = $this->indicatorReport($meta);
        }

        return $out;
    }

    private function indicatorReport(array $meta): array
    {
        $base = [
            'code' => $meta['code'], 'key' => $meta['key'], 'label' => $meta['label'],
            'group' => $meta['group'], 'level' => $meta['level'], 'type' => $meta['type'],
            'status' => $meta['status'], 'note' => $meta['note'] ?? null,
            'no_period' => $meta['no_period'] ?? false,
        ];

        if ($meta['status'] === 'blocked') {
            return $base + ['total' => null, 'by' => []];
        }

        $spec = $this->rowsetSpec($meta['key']);
        if ($spec === null) {
            return $base + ['total' => null, 'by' => []];
        }

        $opt = [
            // age_at_end: point-in-time status indicators (rowset already
            // bounded by period end); evaluated like no-period indicators.
            'no_period' => ($meta['no_period'] ?? false) || ($spec['age_at_end'] ?? false),
            'skip_period' => $spec['skip_period'] ?? false,
            'instrument' => $spec['service_point'] ?? false,
            'flag' => $spec['mode'] === 'rate_flag',
            'val' => $spec['mode'] === 'sum',
        ];

        if ($spec['mode'] === 'rate_pair') {
            $num = $this->detailRows($spec['num'], ['flag' => false, 'val' => false] + $opt);
            $den = $this->detailRows($spec['den'], ['flag' => false, 'val' => false] + $opt);

            return $base + [
                'total' => $this->pairBucket($num, $den),
                'by' => [
                    'age_band' => $this->pairBuckets($num, $den, 'age_band'),
                    'sex' => $this->pairBuckets($num, $den, 'sex'),
                    'facility' => $this->pairBuckets($num, $den, 'facility'),
                    'district' => $this->pairBuckets($num, $den, 'district'),
                ],
            ];
        }

        $rows = $this->detailRows($spec['sql'], $opt);

        $agg = fn (array $subset) => $this->aggregate($subset, $spec['mode']);
        $by = [
            'age_band' => $this->buckets($rows, 'age_band', $agg),
            'sex' => $this->buckets($rows, 'sex', $agg),
            'facility' => $this->buckets($rows, 'facility', $agg),
            'district' => $this->buckets($rows, 'district', $agg),
        ];
        if ($spec['service_point'] ?? false) {
            $by['service_point'] = $this->buckets($rows, 'instrument', $agg);
        }

        return $base + ['total' => $agg($rows), 'by' => $by];
    }

    // ------------------------------------------------------------------
    // Detail-row fetching and PHP aggregation
    // ------------------------------------------------------------------

    /**
     * Wraps a rowset (record, ref_date [, flag][, val][, instrument]) with
     * the demog join, adolescent age filter, period filter, and dimension
     * columns; returns plain arrays.
     */
    private function detailRows(string $rowset, array $opt): array
    {
        [$demog, $bind] = $this->demogSql();
        $noPeriod = $opt['no_period'] ?? false;
        $refExpr = $noPeriod ? "'{$this->to}'" : 'b.ref_date';
        $period = ($noPeriod || ($opt['skip_period'] ?? false)) ? '1 = 1' : $this->periodCond('b.ref_date');
        $age = $this->ageCond($refExpr);
        $flagCol = ($opt['flag'] ?? false) ? 'COALESCE(b.flag, 0)' : '0';
        $valCol = ($opt['val'] ?? false) ? 'COALESCE(b.val, 0)' : '0';
        $instCol = ($opt['instrument'] ?? false) ? ', b.instrument' : '';

        $rows = DB::select("
            WITH demog AS ({$demog}), base AS ({$rowset})
            SELECT b.record,
                   COALESCE(NULLIF(d.facility, ''), 'Unknown') AS facility,
                   COALESCE(NULLIF(d.district, ''), 'Unknown') AS district,
                   CASE d.gender WHEN '1' THEN 'Male' WHEN '2' THEN 'Female' ELSE 'Unknown' END AS sex,
                   CASE WHEN TIMESTAMPDIFF(YEAR, d.dob, {$refExpr}) BETWEEN 10 AND 14 THEN '10-14'
                        WHEN TIMESTAMPDIFF(YEAR, d.dob, {$refExpr}) BETWEEN 15 AND 19 THEN '15-19'
                        ELSE 'Other' END AS age_band,
                   {$flagCol} AS flag,
                   {$valCol} AS val
                   {$instCol}
            FROM base b
            JOIN demog d ON d.record = b.record
            WHERE {$period} AND {$age}
        ", $bind);

        return array_map(fn ($r) => (array) $r, $rows);
    }

    private function aggregate(array $rows, string $mode): array
    {
        return match ($mode) {
            'distinct' => ['value' => count(array_unique(array_column($rows, 'record')))],
            'row' => ['value' => count($rows)],
            'sum' => ['value' => (int) array_sum(array_column($rows, 'val'))],
            'rate_flag' => $this->flagRate($rows),
            default => ['value' => null],
        };
    }

    private function flagRate(array $rows): array
    {
        $den = count($rows);
        $num = count(array_filter($rows, fn ($r) => (int) $r['flag'] === 1));

        return [
            'value' => $den > 0 ? round($num / $den * 100, 1) : null,
            'numerator' => $num,
            'denominator' => $den,
        ];
    }

    private function buckets(array $rows, string $dim, callable $agg): array
    {
        $groups = [];
        foreach ($rows as $row) {
            $groups[$row[$dim] ?? 'Unknown'][] = $row;
        }
        ksort($groups);

        $out = [];
        foreach ($groups as $label => $subset) {
            $out[] = ['label' => (string) $label] + $agg($subset);
        }

        return $out;
    }

    private function pairBucket(array $num, array $den): array
    {
        $n = count(array_unique(array_column($num, 'record')));
        $d = count(array_unique(array_column($den, 'record')));

        return [
            'value' => $d > 0 ? round($n / $d * 100, 1) : null,
            'numerator' => $n,
            'denominator' => $d,
        ];
    }

    private function pairBuckets(array $num, array $den, string $dim): array
    {
        $labels = array_unique(array_merge(array_column($num, $dim), array_column($den, $dim)));
        sort($labels);

        $out = [];
        foreach ($labels as $label) {
            $out[] = ['label' => (string) $label] + $this->pairBucket(
                array_filter($num, fn ($r) => $r[$dim] === $label),
                array_filter($den, fn ($r) => $r[$dim] === $label),
            );
        }

        return $out;
    }

    // ------------------------------------------------------------------
    // Rowset SQL per indicator key
    // ------------------------------------------------------------------

    private function rowsetSpec(string $key): ?array
    {
        $enc = $this->encountersSql();
        $dateRe = self::$DATE_RE;
        $numRe = self::$NUM_RE;

        $hts = fn () => $this->pivotSql(self::$P_ALL, ['tested' => 'hts_tested', 'test_date' => 'hts_hiv_date', 'result' => 'hts_hiv_result', 'art_init' => 'hts_art_init']);
        $art = fn () => $this->pivotSql(self::$P_ART, [
            'visit_date' => 'art_review_date', 'next_visit' => 'art_next_review_date', 'outcome' => 'art_final_outcome',
            'vl_done' => 'art_viral_load', 'vl_date' => 'art_vl_collect_date', 'vl_detected' => 'art_vl_detected', 'vl_result' => 'art_vl_result',
        ]);
        $artr = fn () => $this->pivotSql(self::$P_ART, ['referred' => 'artr_referred', 'reg_date' => 'artr_registration_date']);
        $ancr = fn () => $this->pivotSql(self::$P_FCH, ['reg_date' => 'ancr_date', 'first_booking' => 'ancr_first_booking', 'hiv_prior' => 'ancr_hiv_prior', 'contact_no' => 'ancr_contact_number']);
        $anc = fn () => $this->pivotSql(self::$P_FCH, ['visit_date' => 'anc_date', 'contact_no' => 'anc_contact_number']);
        $pncr = fn () => $this->pivotSql(self::$P_FCH, ['reg_date' => 'pncr_date', 'place' => 'pncr_place_of_delivery', 'hiv_post' => 'pncr_hiv_status_post', 'on_art' => 'pncr_hiv_status_art', 'baby_dob' => 'pncr_date_of_birth']);
        $pncm = fn () => $this->pivotSql(self::$P_FCH, ['visit_date' => 'pncm_visit_date', 'follow_up' => 'pncm_mother_follow_up', 'hiv_tested' => 'pncm_hiv_tested']);
        $pncb = fn () => $this->pivotSql(self::$P_FCH, ['visit_date' => 'pncb_visit_date', 'infant_status' => 'pncb_infant_follow_ups']);
        $fp = fn () => $this->pivotSql(self::$P_FCH, ['visit_date' => 'fp_date', 'category' => 'fp_client_category']);
        $prepr = fn () => $this->pivotSql(self::$P_ALL, ['reg_date' => 'prepr_date', 'screened' => 'prepr_screened', 'visit_status' => 'prepr_visit_status', 'initiate' => 'prepr_prep_initiate', 'start_date' => 'prepr_prep_start_date']);
        $mh = fn () => $this->pivotSql(self::$P_ALL, ['screened' => 'mh_screening_tools', 'result' => 'mh_screening_results', 'managed' => 'mh_management_outcome', 'substance' => 'mh_substance_identified']);
        $sti = fn () => $this->pivotSql(self::$P_ALL, ['visit_date' => 'sti_visit_date', 'alt_date' => 'sti_date', 'treated' => 'sti_patient_treated']);
        $pls = fn () => $this->pivotSql(self::$P_ALL, ['session_date' => 'pls_date', 'conducted' => 'pls_session_conducted', 'sessions' => 'pls_number', 'reached' => 'pls_ado_number', 'support' => 'pls_support_conducted']);

        $htsTested = "SELECT record, test_date AS ref_date FROM ({$hts()}) p WHERE p.tested = '1'";
        $stiHiv = fn () => $this->pivotSql(self::$P_ALL, ['tested' => 'sti_hiv_test', 'test_date' => 'sti_visit_date', 'result' => 'sti_hiv_test_result']);
        $prepHiv = fn () => $this->pivotSql(self::$P_ALL, ['tested' => 'prep_hiv_test', 'test_date' => 'prep_visit_date', 'result' => 'prep_hiv_test_results']);
        $ancHiv = fn () => $this->pivotSql(self::$P_FCH, ['result' => 'anc_hiv_test_results', 'test_date' => 'anc_date']);
        $artrHiv = fn () => $this->pivotSql(self::$P_ART, ['first_test' => 'artr_first_hiv_test']);
        $allTests = fn (string $where) => "SELECT record, test_date AS ref_date FROM (
                SELECT record, test_date, result FROM ({$hts()}) a WHERE a.tested = '1'
                UNION ALL
                SELECT record, test_date, result FROM ({$stiHiv()}) b WHERE b.tested = '1'
                UNION ALL
                SELECT record, test_date, result FROM ({$prepHiv()}) c WHERE c.tested = '1'
                UNION ALL
                SELECT record, test_date, result FROM ({$ancHiv()}) e WHERE e.result IN ('P','N')
                UNION ALL
                SELECT record, first_test AS test_date, 'P' AS result FROM ({$artrHiv()}) g
                WHERE g.first_test REGEXP '{$dateRe}'
            ) t {$where}";
        $vlDate = "COALESCE(NULLIF(p.vl_date, ''), p.visit_date)";
        $vlTested = "SELECT record, {$vlDate} AS ref_date FROM ({$art()}) p WHERE p.vl_done = '1'";
        $inits = "SELECT record, MIN(test_date) AS ref_date FROM ({$hts()}) p WHERE p.art_init = 'Y' AND p.test_date REGEXP '{$dateRe}' GROUP BY record";
        $deliveries = fn (string $extra) => "SELECT p.record, p.reg_date AS ref_date{$extra} FROM ({$pncr()}) p";

        return match ($key) {
            'access_clients' => ['mode' => 'distinct', 'service_point' => true,
                'sql' => "SELECT record, visit_date AS ref_date, instrument FROM ({$enc}) e"],
            'access_first' => ['mode' => 'distinct',
                'sql' => "SELECT record, MIN(visit_date) AS ref_date FROM ({$enc}) e GROUP BY record"],
            'access_repeat' => ['mode' => 'row',
                'sql' => "SELECT e.record, e.visit_date AS ref_date
                          FROM ({$enc}) e
                          JOIN (SELECT record, MIN(visit_date) AS fv FROM ({$enc}) x GROUP BY record) f ON f.record = e.record
                          WHERE e.visit_date > f.fv"],

            'hiv_tested' => ['mode' => 'distinct', 'sql' => $htsTested],
            'hiv_positive' => ['mode' => 'distinct',
                'sql' => "SELECT record, test_date AS ref_date FROM ({$hts()}) p WHERE p.tested = '1' AND p.result = 'P'"],
            'hiv_positivity' => ['mode' => 'rate_pair',
                'num' => "SELECT record, test_date AS ref_date FROM ({$hts()}) p WHERE p.tested = '1' AND p.result = 'P'",
                'den' => $htsTested],
            'hiv_tested_all' => ['mode' => 'distinct', 'sql' => $allTests('')],
            'hiv_positive_all' => ['mode' => 'distinct', 'sql' => $allTests("WHERE t.result = 'P'")],
            'hiv_positivity_all' => ['mode' => 'rate_pair',
                'num' => $allTests("WHERE t.result = 'P'"),
                'den' => $allTests('')],

            'art_initiated' => ['mode' => 'distinct', 'sql' => $inits],
            'art_current' => ['mode' => 'distinct', 'age_at_end' => true,
                'sql' => "SELECT record, visit_date AS ref_date FROM (
                              SELECT p.*, ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date DESC) AS rn
                              FROM ({$art()}) p WHERE p.visit_date REGEXP '{$dateRe}' AND p.visit_date <= '{$this->to}'
                          ) l
                          WHERE l.rn = 1 AND (l.outcome IS NULL OR l.outcome NOT IN ('3','4','5','6'))
                            AND l.next_visit REGEXP '{$dateRe}' AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) >= '{$this->to}'"],
            'art_retention' => ['mode' => 'rate_flag', 'skip_period' => true,
                'sql' => "SELECT i.record, i.ref_date,
                                 CASE WHEN EXISTS (
                                     SELECT 1 FROM ({$art()}) v
                                     WHERE v.record = i.record AND v.visit_date REGEXP '{$dateRe}'
                                       AND v.visit_date BETWEEN DATE_ADD(i.ref_date, INTERVAL 270 DAY) AND DATE_ADD(i.ref_date, INTERVAL 455 DAY)
                                 ) AND NOT EXISTS (
                                     SELECT 1 FROM ({$art()}) o
                                     WHERE o.record = i.record AND o.outcome IN ('4','5') AND o.visit_date REGEXP '{$dateRe}'
                                       AND o.visit_date <= DATE_ADD(i.ref_date, INTERVAL 365 DAY)
                                 ) THEN 1 ELSE 0 END AS flag
                          FROM (SELECT record, ref_date FROM ({$inits}) x
                                WHERE ref_date BETWEEN DATE_SUB('{$this->from}', INTERVAL 12 MONTH) AND DATE_SUB('{$this->to}', INTERVAL 12 MONTH)) i"],
            'art_ltfu' => ['mode' => 'distinct', 'age_at_end' => true,
                'sql' => "SELECT record, visit_date AS ref_date FROM (
                              SELECT p.*, ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date DESC) AS rn
                              FROM ({$art()}) p WHERE p.visit_date REGEXP '{$dateRe}' AND p.visit_date <= '{$this->to}'
                          ) l
                          WHERE l.rn = 1 AND (l.outcome IS NULL OR l.outcome NOT IN ('4','5'))
                            AND l.next_visit REGEXP '{$dateRe}' AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) < '{$this->to}'"],
            'art_ti' => ['mode' => 'distinct',
                'sql' => "SELECT record, reg_date AS ref_date FROM ({$artr()}) p WHERE p.referred = '1'"],
            'art_to' => ['mode' => 'distinct',
                'sql' => "SELECT record, visit_date AS ref_date FROM ({$art()}) p WHERE p.outcome = '4'"],
            'art_died' => ['mode' => 'distinct',
                'sql' => "SELECT record, visit_date AS ref_date FROM ({$art()}) p WHERE p.outcome = '5'"],
            'art_vl_tested' => ['mode' => 'distinct', 'sql' => $vlTested],
            'art_vl_tnd' => ['mode' => 'distinct',
                'sql' => "SELECT record, {$vlDate} AS ref_date FROM ({$art()}) p WHERE p.vl_done = '1' AND p.vl_detected = '0'"],
            'art_vl_suppressed' => ['mode' => 'rate_pair',
                'num' => "SELECT record, {$vlDate} AS ref_date FROM ({$art()}) p
                          WHERE p.vl_done = '1' AND (p.vl_detected = '0' OR (p.vl_result REGEXP '{$numRe}' AND CAST(p.vl_result AS UNSIGNED) < 1000))",
                'den' => $vlTested],
            'art_vl_high' => ['mode' => 'distinct',
                'sql' => "SELECT record, {$vlDate} AS ref_date FROM ({$art()}) p
                          WHERE p.vl_result REGEXP '{$numRe}' AND CAST(p.vl_result AS UNSIGNED) >= 1000"],

            'anc_new' => ['mode' => 'distinct',
                'sql' => "SELECT record, reg_date AS ref_date FROM ({$ancr()}) p WHERE p.first_booking = '1'"],
            'anc_8plus' => ['mode' => 'rate_flag',
                'sql' => $deliveries(", CASE WHEN c.max_contact >= 8 THEN 1 ELSE 0 END AS flag")."
                          LEFT JOIN (
                              SELECT record, MAX(CASE WHEN contact_no REGEXP '{$numRe}' THEN CAST(contact_no AS UNSIGNED) ELSE 0 END) AS max_contact
                              FROM (SELECT record, contact_no FROM ({$ancr()}) a UNION ALL SELECT record, contact_no FROM ({$anc()}) b) u
                              GROUP BY record
                          ) c ON c.record = p.record"],
            'births_inst' => ['mode' => 'row',
                'sql' => "SELECT p.record, p.reg_date AS ref_date FROM ({$pncr()}) p WHERE p.place = '1'"],
            'births_home' => ['mode' => 'row',
                'sql' => "SELECT p.record, p.reg_date AS ref_date FROM ({$pncr()}) p WHERE p.place IN ('2','3')"],
            'stillbirths' => null,
            'neonatal_deaths' => ['mode' => 'row',
                'sql' => "SELECT b.record, b.visit_date AS ref_date
                          FROM ({$pncb()}) b
                          JOIN (SELECT record, MAX(baby_dob) AS baby_dob FROM ({$pncr()}) r WHERE baby_dob REGEXP '{$dateRe}' GROUP BY record) bd ON bd.record = b.record
                          WHERE b.infant_status = '6' AND b.visit_date REGEXP '{$dateRe}'
                            AND DATEDIFF(b.visit_date, bd.baby_dob) BETWEEN 0 AND 7"],
            'maternal_deaths' => ['mode' => 'distinct',
                'sql' => "SELECT record, visit_date AS ref_date FROM ({$pncm()}) p WHERE p.follow_up = '5'"],
            'pnc_72h' => ['mode' => 'rate_flag',
                'sql' => $deliveries(", CASE WHEN pn.record IS NOT NULL THEN 1 ELSE 0 END AS flag")."
                          LEFT JOIN (
                              SELECT DISTINCT m.record FROM ({$pncm()}) m
                              JOIN ({$pncr()}) r ON r.record = m.record
                              WHERE m.visit_date REGEXP '{$dateRe}' AND r.baby_dob REGEXP '{$dateRe}'
                                AND DATEDIFF(m.visit_date, r.baby_dob) BETWEEN 0 AND 3
                          ) pn ON pn.record = p.record"],
            'anc_first_tested' => ['mode' => 'rate_flag',
                'sql' => "SELECT record, reg_date AS ref_date, CASE WHEN hiv_prior IN ('0','1') THEN 1 ELSE 0 END AS flag
                          FROM ({$ancr()}) p WHERE p.first_booking = '1'"],
            'bf_retest' => ['mode' => 'rate_pair',
                'num' => "SELECT m.record, m.visit_date AS ref_date FROM ({$pncm()}) m
                          JOIN (SELECT DISTINCT record FROM ({$pncr()}) r WHERE r.hiv_post = 'N') n ON n.record = m.record
                          WHERE m.hiv_tested = '1'",
                'den' => "SELECT m.record, m.visit_date AS ref_date FROM ({$pncm()}) m
                          JOIN (SELECT DISTINCT record FROM ({$pncr()}) r WHERE r.hiv_post = 'N') n ON n.record = m.record"],
            'art_at_delivery' => ['mode' => 'rate_flag',
                'sql' => "SELECT p.record, p.reg_date AS ref_date, CASE WHEN p.on_art = '1' THEN 1 ELSE 0 END AS flag
                          FROM ({$pncr()}) p WHERE p.hiv_post = 'P'"],

            'fp_new' => ['mode' => 'distinct',
                'sql' => "SELECT record, visit_date AS ref_date FROM ({$fp()}) p WHERE p.category = 'N'"],
            'fp_repeat' => ['mode' => 'distinct',
                'sql' => "SELECT record, visit_date AS ref_date FROM ({$fp()}) p WHERE p.category = 'R'"],
            'prep_screened' => ['mode' => 'distinct',
                'sql' => "SELECT record, reg_date AS ref_date FROM ({$prepr()}) p WHERE p.screened = '1'"],
            'prep_initiated' => ['mode' => 'distinct',
                'sql' => "SELECT record, COALESCE(NULLIF(start_date, ''), reg_date) AS ref_date FROM ({$prepr()}) p
                          WHERE p.visit_status = 'N' OR p.initiate = '1'"],
            'prep_continuing' => ['mode' => 'distinct',
                'sql' => "SELECT record, reg_date AS ref_date FROM ({$prepr()}) p WHERE p.visit_status IN ('C','N')"],
            'prep_discontinued' => ['mode' => 'distinct',
                'sql' => "SELECT record, reg_date AS ref_date FROM ({$prepr()}) p WHERE p.visit_status = 'D'"],

            'mh_screened' => ['mode' => 'distinct',
                'sql' => "SELECT record, NULL AS ref_date FROM ({$mh()}) p WHERE p.screened = '1'"],
            'mh_positive' => ['mode' => 'distinct',
                'sql' => "SELECT record, NULL AS ref_date FROM ({$mh()}) p WHERE p.result = 'P'"],
            'mh_managed' => ['mode' => 'distinct',
                'sql' => "SELECT record, NULL AS ref_date FROM ({$mh()}) p WHERE p.managed IN ('R','M','B')"],
            'su_screened' => ['mode' => 'distinct',
                'sql' => "SELECT record, NULL AS ref_date FROM ({$mh()}) p WHERE p.screened = '1' AND p.substance IN ('0','1')"],
            'su_positive' => ['mode' => 'distinct',
                'sql' => "SELECT record, NULL AS ref_date FROM ({$mh()}) p WHERE p.substance = '1'"],
            'su_managed' => ['mode' => 'distinct',
                'sql' => "SELECT record, NULL AS ref_date FROM ({$mh()}) p WHERE p.substance = '1' AND p.managed IN ('R','M','B')"],

            'sti_screened' => ['mode' => 'distinct',
                'sql' => "SELECT record, COALESCE(NULLIF(visit_date, ''), alt_date) AS ref_date FROM ({$sti()}) p"],
            'sti_treated' => ['mode' => 'distinct',
                'sql' => "SELECT record, COALESCE(NULLIF(visit_date, ''), alt_date) AS ref_date FROM ({$sti()}) p WHERE p.treated = '1'"],

            'peer_sessions' => ['mode' => 'sum',
                'sql' => "SELECT record, session_date AS ref_date,
                                 CASE WHEN sessions REGEXP '{$numRe}' THEN CAST(sessions AS UNSIGNED) ELSE 0 END AS val
                          FROM (SELECT DISTINCT record, session_date, inst, conducted, sessions FROM ({$pls()}) raw) p
                          WHERE p.conducted = '1'"],
            'peer_reached' => ['mode' => 'sum',
                'sql' => "SELECT record, session_date AS ref_date,
                                 CASE WHEN reached REGEXP '{$numRe}' THEN CAST(reached AS UNSIGNED) ELSE 0 END AS val
                          FROM (SELECT DISTINCT record, session_date, inst, conducted, reached FROM ({$pls()}) raw) p
                          WHERE p.conducted = '1'"],
            'support_groups' => ['mode' => 'row',
                'sql' => "SELECT record, session_date AS ref_date
                          FROM (SELECT DISTINCT record, session_date, inst, support FROM ({$pls()}) raw) p
                          WHERE p.support = '1'"],

            default => null,
        };
    }
}
