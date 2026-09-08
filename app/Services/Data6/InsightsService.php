<?php

namespace App\Services\Data6;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Cross-service insights for programme managers / M&E: how adolescents move
 * between services (linkage, co-utilisation, journeys, cascades) rather than
 * single-indicator counts.
 *
 * Core building block: a per-client service profile for the period - every
 * dated encounter (all instruments) plus the three dateless instruments
 * (mental health, health education, counselling: access flags, all-time).
 * Adolescent = 10-19 at the period end. Patient key = record (shared across
 * projects); mirrored copies are deduplicated by the encounter union.
 */
class InsightsService
{
    use QueryFragments;

    private string $from;

    private string $to;

    private ?string $district = null;

    private ?string $facility = null;

    private ?string $gender = null;

    private ?int $ageLo = 10;

    private ?int $ageHi = 19;

    private const DATELESS = ['Mental health', 'Health education', 'Counselling'];

    public function compute(string $from, string $to): array
    {
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {
            throw new InvalidArgumentException('Invalid period.');
        }
        $this->from = $from;
        $this->to = $to;

        $profiles = $this->profiles();

        return [
            'period' => ['from' => $from, 'to' => $to],
            'overview' => $this->overview($profiles),
            'co_utilisation' => $this->coUtilisation($profiles),
            'uptake_by_group' => $this->uptakeByGroup($profiles),
            'entry_points' => $this->entryPoints($profiles),
            'transitions' => $this->transitions($profiles),
            'engagement' => $this->engagement($profiles),
            'facility_integration' => $this->facilityIntegration($profiles),
            'sti_pathways' => $this->pathwaysFrom($profiles, 'STI'),
            'hts_pathways' => $this->pathwaysFrom($profiles, 'HIV testing'),
            'opd_pathways' => $this->pathwaysFrom($profiles, 'Outpatient'),
            'hiv_cascade' => $this->hivCascade(),
            'prep_cascade' => $this->prepCascade(),
            'anc_continuum' => $this->ancContinuum($profiles),
            'mh_pathway' => $this->mhPathway(),
        ];
    }

    // ------------------------------------------------------------------
    // Per-client service profile
    // ------------------------------------------------------------------

    /**
     * @return array<string, array{facility:string,district:string,sex:string,age_band:string,
     *   families:array<string,true>, events:list<array{family:string,date:string}>, first_ever:?string}>
     */
    private function profiles(): array
    {
        [$demog, $bind] = $this->demogSql();
        $enc = $this->encountersSql();

        $rows = DB::select("
            WITH demog AS ({$demog}), enc AS ({$enc}),
            firsts AS (SELECT record, MIN(visit_date) AS first_ever FROM enc GROUP BY record),
            dated AS (
                SELECT e.record, e.instrument, e.visit_date FROM enc e WHERE {$this->periodCond('e.visit_date')}
            ),
            flags AS (
                SELECT DISTINCT record, SUBSTRING_INDEX(field_name, '_', 1) AS instrument, NULL AS visit_date
                FROM redcap_data6
                WHERE project_id IN (".self::$P_ALL.")
                  AND field_name IN ('mh_access', 'he_access', 'couns_access') AND value = 'Y'
            ),
            allsvc AS (SELECT * FROM dated UNION ALL SELECT * FROM flags)
            SELECT s.record, s.instrument, s.visit_date,
                   COALESCE(NULLIF(d.facility, ''), 'Unknown') AS facility,
                   COALESCE(NULLIF(d.district, ''), 'Unknown') AS district,
                   CASE d.gender WHEN '1' THEN 'Male' WHEN '2' THEN 'Female' ELSE 'Unknown' END AS sex,
                   CASE WHEN TIMESTAMPDIFF(YEAR, d.dob, '{$this->to}') BETWEEN 10 AND 14 THEN '10-14' ELSE '15-19' END AS age_band,
                   f.first_ever
            FROM allsvc s
            JOIN demog d ON d.record = s.record
            LEFT JOIN firsts f ON f.record = s.record
            WHERE {$this->ageAtPeriodEnd()}
            ORDER BY s.record, s.visit_date
        ", $bind);

        $profiles = [];
        foreach ($rows as $r) {
            $family = $this->familyLabel($r->instrument);
            if ($family === null) {
                continue;
            }
            $p = &$profiles[$r->record];
            if (! isset($p['record'])) {
                $p = [
                    'record' => $r->record, 'facility' => $r->facility, 'district' => $r->district,
                    'sex' => $r->sex, 'age_band' => $r->age_band, 'first_ever' => $r->first_ever,
                    'families' => [], 'events' => [],
                ];
            }
            $p['families'][$family] = true;
            if ($r->visit_date !== null) {
                $p['events'][] = ['family' => $family, 'date' => $r->visit_date];
            }
            unset($p);
        }

        // Drop clients whose only presence is a dateless flag with no visit in
        // the period - they were not "seen" in this period.
        return array_filter($profiles, fn ($p) => $p['events'] !== []);
    }

    private function familyLabels(): array
    {
        return array_values(array_unique(array_values(self::$FAMILY)));
    }

    // ------------------------------------------------------------------
    // Insights derived from the profile
    // ------------------------------------------------------------------

    private function overview(array $profiles): array
    {
        $total = count($profiles);
        $dist = ['1' => 0, '2' => 0, '3' => 0, '4+' => 0];
        $familyCounts = array_fill_keys($this->familyLabels(), 0);
        $sumFamilies = 0;

        foreach ($profiles as $p) {
            $n = count($p['families']);
            $sumFamilies += $n;
            $dist[$n >= 4 ? '4+' : (string) $n]++;
            foreach (array_keys($p['families']) as $f) {
                $familyCounts[$f]++;
            }
        }
        arsort($familyCounts);

        return [
            'clients' => $total,
            'avg_services' => $total ? round($sumFamilies / $total, 2) : 0,
            'multi_service_share' => $total ? round(($total - $dist['1']) / $total * 100, 1) : 0,
            'services_per_client' => array_map(fn ($k, $v) => ['label' => $k.' service'.((string) $k === '1' ? '' : 's'), 'count' => $v], array_keys($dist), $dist),
            'clients_per_service' => array_map(fn ($k, $v) => ['label' => $k, 'count' => $v, 'dateless' => in_array($k, self::DATELESS, true)], array_keys($familyCounts), $familyCounts),
        ];
    }

    /** matrix[row][col] = % of clients with row-service who also used col-service. */
    private function coUtilisation(array $profiles): array
    {
        $labels = [];
        foreach ($profiles as $p) {
            foreach (array_keys($p['families']) as $f) {
                $labels[$f] = ($labels[$f] ?? 0) + 1;
            }
        }
        arsort($labels);
        $order = array_keys($labels);

        $pair = [];
        foreach ($profiles as $p) {
            $fams = array_keys($p['families']);
            foreach ($fams as $a) {
                foreach ($fams as $b) {
                    if ($a !== $b) {
                        $pair[$a][$b] = ($pair[$a][$b] ?? 0) + 1;
                    }
                }
            }
        }

        $matrix = [];
        foreach ($order as $a) {
            $row = ['service' => $a, 'clients' => $labels[$a], 'cells' => []];
            foreach ($order as $b) {
                $n = $a === $b ? $labels[$a] : ($pair[$a][$b] ?? 0);
                $row['cells'][] = ['service' => $b, 'count' => $n, 'pct' => $labels[$a] ? round($n / $labels[$a] * 100, 1) : 0];
            }
            $matrix[] = $row;
        }

        return ['services' => $order, 'rows' => $matrix];
    }

    /** For each service: share of its clients by sex and age band. */
    private function uptakeByGroup(array $profiles): array
    {
        $out = [];
        foreach ($profiles as $p) {
            foreach (array_keys($p['families']) as $f) {
                $out[$f]['total'] = ($out[$f]['total'] ?? 0) + 1;
                $out[$f]['sex'][$p['sex']] = ($out[$f]['sex'][$p['sex']] ?? 0) + 1;
                $out[$f]['age'][$p['age_band']] = ($out[$f]['age'][$p['age_band']] ?? 0) + 1;
            }
        }
        uasort($out, fn ($a, $b) => $b['total'] <=> $a['total']);

        $rows = [];
        foreach ($out as $service => $d) {
            $rows[] = [
                'service' => $service,
                'clients' => $d['total'],
                'female_pct' => round(($d['sex']['Female'] ?? 0) / $d['total'] * 100, 1),
                'male_pct' => round(($d['sex']['Male'] ?? 0) / $d['total'] * 100, 1),
                'young_pct' => round(($d['age']['10-14'] ?? 0) / $d['total'] * 100, 1),
                'older_pct' => round(($d['age']['15-19'] ?? 0) / $d['total'] * 100, 1),
            ];
        }

        return $rows;
    }

    /** First service used by clients whose first-ever visit falls in the period. */
    private function entryPoints(array $profiles): array
    {
        $counts = [];
        $new = 0;
        foreach ($profiles as $p) {
            if ($p['first_ever'] === null || $p['first_ever'] < $this->from || $p['first_ever'] > $this->to) {
                continue;
            }
            $new++;
            $first = $p['events'][0]['family'] ?? null;
            if ($first !== null) {
                $counts[$first] = ($counts[$first] ?? 0) + 1;
            }
        }
        arsort($counts);

        return [
            'new_clients' => $new,
            'services' => array_map(fn ($k, $v) => ['label' => $k, 'count' => $v, 'pct' => $new ? round($v / $new * 100, 1) : 0], array_keys($counts), $counts),
        ];
    }

    /** Most common consecutive service pairs (A then a different B) in the period. */
    private function transitions(array $profiles): array
    {
        $pairs = [];
        $clientsWithTransition = 0;
        foreach ($profiles as $p) {
            $seen = [];
            $prev = null;
            $had = false;
            foreach ($p['events'] as $e) {
                if ($prev !== null && $prev !== $e['family']) {
                    $key = $prev.' → '.$e['family'];
                    if (! isset($seen[$key])) {
                        $pairs[$key] = ($pairs[$key] ?? 0) + 1;
                        $seen[$key] = true;
                        $had = true;
                    }
                }
                $prev = $e['family'];
            }
            if ($had) {
                $clientsWithTransition++;
            }
        }
        arsort($pairs);

        return [
            'clients_with_transition' => $clientsWithTransition,
            'top' => array_slice(array_map(fn ($k, $v) => ['label' => $k, 'count' => $v], array_keys($pairs), $pairs), 0, 12),
        ];
    }

    private function engagement(array $profiles): array
    {
        $dist = ['1 visit' => 0, '2 visits' => 0, '3-5 visits' => 0, '6+ visits' => 0];
        $visits = [];
        $returning = 0;
        foreach ($profiles as $p) {
            $n = count($p['events']);
            $visits[] = $n;
            if ($n >= 2) {
                $returning++;
            }
            $dist[$n === 1 ? '1 visit' : ($n === 2 ? '2 visits' : ($n <= 5 ? '3-5 visits' : '6+ visits'))]++;
        }
        sort($visits);
        $total = count($visits);
        $median = $total ? $visits[intdiv($total, 2)] : 0;

        return [
            'clients' => $total,
            'returning' => $returning,
            'returning_pct' => $total ? round($returning / $total * 100, 1) : 0,
            'median_visits' => $median,
            'total_visits' => array_sum($visits),
            'distribution' => array_map(fn ($k, $v) => ['label' => $k, 'count' => $v], array_keys($dist), $dist),
        ];
    }

    private function facilityIntegration(array $profiles): array
    {
        $fac = [];
        foreach ($profiles as $p) {
            $f = &$fac[$p['facility']];
            $f['clients'] = ($f['clients'] ?? 0) + 1;
            $f['services'] = ($f['services'] ?? 0) + count($p['families']);
            $f['multi'] = ($f['multi'] ?? 0) + (count($p['families']) > 1 ? 1 : 0);
            unset($f);
        }
        $rows = [];
        foreach ($fac as $name => $d) {
            $rows[] = [
                'facility' => $name, 'clients' => $d['clients'],
                'avg_services' => round($d['services'] / $d['clients'], 2),
                'multi_service_pct' => round($d['multi'] / $d['clients'] * 100, 1),
            ];
        }
        usort($rows, fn ($a, $b) => $b['avg_services'] <=> $a['avg_services']);

        return $rows;
    }

    /** Of clients who used $origin in the period: share who also used each other service. */
    private function pathwaysFrom(array $profiles, string $origin): array
    {
        $base = array_filter($profiles, fn ($p) => isset($p['families'][$origin]));
        $n = count($base);
        $also = [];
        $after = [];
        foreach ($base as $p) {
            $originFirst = null;
            foreach ($p['events'] as $e) {
                if ($e['family'] === $origin) {
                    $originFirst = $e['date'];
                    break;
                }
            }
            foreach (array_keys($p['families']) as $f) {
                if ($f === $origin) {
                    continue;
                }
                $also[$f] = ($also[$f] ?? 0) + 1;
            }
            $seenAfter = [];
            foreach ($p['events'] as $e) {
                if ($originFirst !== null && $e['family'] !== $origin && $e['date'] >= $originFirst && ! isset($seenAfter[$e['family']])) {
                    $after[$e['family']] = ($after[$e['family']] ?? 0) + 1;
                    $seenAfter[$e['family']] = true;
                }
            }
        }
        arsort($also);

        return [
            'origin' => $origin,
            'clients' => $n,
            'services' => array_map(fn ($k, $v) => [
                'label' => $k, 'count' => $v, 'pct' => $n ? round($v / $n * 100, 1) : 0,
                'after_count' => $after[$k] ?? 0, 'after_pct' => $n ? round(($after[$k] ?? 0) / $n * 100, 1) : 0,
                'dateless' => in_array($k, self::DATELESS, true),
            ], array_keys($also), $also),
        ];
    }

    // ------------------------------------------------------------------
    // SQL-based cascades
    // ------------------------------------------------------------------

    /** Tested (all entry points) -> positive -> linked to ART -> VL tested -> suppressed, with time to ART. */
    private function hivCascade(): array
    {
        [$demog, $bind] = $this->demogSql();
        $dateRe = self::$DATE_RE;

        $hts = $this->pivotSql(self::$P_ALL, ['tested' => 'hts_tested', 'test_date' => 'hts_hiv_date', 'result' => 'hts_hiv_result', 'art_init' => 'hts_art_init']);
        $sti = $this->pivotSql(self::$P_ALL, ['tested' => 'sti_hiv_test', 'test_date' => 'sti_visit_date', 'result' => 'sti_hiv_test_result']);
        $prep = $this->pivotSql(self::$P_ALL, ['tested' => 'prep_hiv_test', 'test_date' => 'prep_visit_date', 'result' => 'prep_hiv_test_results']);
        $anc = $this->pivotSql(self::$P_FCH, ['result' => 'anc_hiv_test_results', 'test_date' => 'anc_date']);
        $artr = $this->pivotSql(self::$P_ART, ['first_test' => 'artr_first_hiv_test', 'reg_date' => 'artr_registration_date']);
        $art = $this->pivotSql(self::$P_ART, ['visit_date' => 'art_review_date', 'vl_done' => 'art_viral_load', 'vl_detected' => 'art_vl_detected', 'vl_result' => 'art_vl_result', 'vl_date' => 'art_vl_collect_date']);

        $rows = DB::select("
            WITH demog AS ({$demog}),
            tests AS (
                SELECT record, test_date, result, art_init FROM ({$hts}) a WHERE a.tested = '1'
                UNION ALL SELECT record, test_date, result, NULL FROM ({$sti}) b WHERE b.tested = '1'
                UNION ALL SELECT record, test_date, result, NULL FROM ({$prep}) c WHERE c.tested = '1'
                UNION ALL SELECT record, test_date, result, NULL FROM ({$anc}) e WHERE e.result IN ('P','N')
                UNION ALL SELECT record, first_test, 'P', NULL FROM ({$artr}) g WHERE g.first_test REGEXP '{$dateRe}'
            ),
            tested AS (
                SELECT t.record FROM tests t JOIN demog d ON d.record = t.record
                WHERE {$this->periodCond('t.test_date')} AND {$this->ageCond('t.test_date')} GROUP BY t.record
            ),
            pos AS (
                SELECT t.record, MIN(t.test_date) AS pos_date, MAX(t.art_init = 'Y') AS hts_init
                FROM tests t JOIN demog d ON d.record = t.record
                WHERE t.result = 'P' AND {$this->periodCond('t.test_date')} AND {$this->ageCond('t.test_date')}
                GROUP BY t.record
            ),
            artdates AS (
                SELECT x.record, MIN(x.dt) AS first_art FROM (
                    SELECT record, reg_date AS dt FROM ({$artr}) r WHERE r.reg_date REGEXP '{$dateRe}'
                    UNION ALL SELECT record, visit_date FROM ({$art}) v WHERE v.visit_date REGEXP '{$dateRe}'
                ) x JOIN pos p ON p.record = x.record
                WHERE x.dt >= DATE_SUB(p.pos_date, INTERVAL 30 DAY)
                GROUP BY x.record
            ),
            vl AS (
                SELECT v.record,
                       MAX(v.vl_done = '1') AS vl_tested,
                       MAX(v.vl_done = '1' AND (v.vl_detected = '0' OR (v.vl_result REGEXP '".self::$NUM_RE."' AND CAST(v.vl_result AS UNSIGNED) < 1000))) AS suppressed
                FROM ({$art}) v JOIN pos p ON p.record = v.record
                WHERE COALESCE(NULLIF(v.vl_date, ''), v.visit_date) >= p.pos_date
                GROUP BY v.record
            )
            SELECT (SELECT COUNT(*) FROM tested) AS tested_total,
                   p.record, p.pos_date, p.hts_init, a.first_art,
                   COALESCE(vl.vl_tested, 0) AS vl_tested, COALESCE(vl.suppressed, 0) AS suppressed,
                   COALESCE(NULLIF(d.facility, ''), 'Unknown') AS facility,
                   CASE d.gender WHEN '1' THEN 'Male' WHEN '2' THEN 'Female' ELSE 'Unknown' END AS sex,
                   CASE WHEN TIMESTAMPDIFF(YEAR, d.dob, p.pos_date) BETWEEN 10 AND 14 THEN '10-14' ELSE '15-19' END AS age_band
            FROM pos p
            JOIN demog d ON d.record = p.record
            LEFT JOIN artdates a ON a.record = p.record
            LEFT JOIN vl ON vl.record = p.record
        ", $bind);

        $testedTotal = $rows[0]->tested_total ?? 0;
        if ($rows === []) {
            $testedTotal = (int) (DB::selectOne("
                WITH demog AS ({$demog}), tests AS (
                    SELECT record, test_date FROM ({$hts}) a WHERE a.tested = '1'
                    UNION ALL SELECT record, test_date FROM ({$sti}) b WHERE b.tested = '1'
                    UNION ALL SELECT record, test_date FROM ({$prep}) c WHERE c.tested = '1'
                    UNION ALL SELECT record, test_date FROM ({$anc}) e WHERE e.result IN ('P','N')
                    UNION ALL SELECT record, first_test FROM ({$artr}) g WHERE g.first_test REGEXP '{$dateRe}'
                )
                SELECT COUNT(DISTINCT t.record) AS n FROM tests t JOIN demog d ON d.record = t.record
                WHERE {$this->periodCond('t.test_date')} AND {$this->ageCond('t.test_date')}
            ", $bind)->n ?? 0);
        }

        $positives = count($rows);
        $linked = 0;
        $vlTested = 0;
        $suppressed = 0;
        $days = [];
        $timeBuckets = ['Same day' => 0, '1-7 days' => 0, '8-30 days' => 0, '31-90 days' => 0, 'Over 90 days' => 0, 'Not linked' => 0];
        $byDim = ['facility' => [], 'sex' => [], 'age_band' => []];

        foreach ($rows as $r) {
            $isLinked = $r->first_art !== null || (int) $r->hts_init === 1;
            $linked += $isLinked ? 1 : 0;
            $vlTested += (int) $r->vl_tested;
            $suppressed += (int) $r->suppressed;

            if ($r->first_art !== null) {
                $d = max(0, (int) ((strtotime($r->first_art) - strtotime($r->pos_date)) / 86400));
                $days[] = $d;
                $timeBuckets[$d === 0 ? 'Same day' : ($d <= 7 ? '1-7 days' : ($d <= 30 ? '8-30 days' : ($d <= 90 ? '31-90 days' : 'Over 90 days')))]++;
            } elseif ($isLinked) {
                $timeBuckets['Same day']++;
            } else {
                $timeBuckets['Not linked']++;
            }

            foreach (['facility', 'sex', 'age_band'] as $dim) {
                $k = $r->{$dim};
                $byDim[$dim][$k]['positives'] = ($byDim[$dim][$k]['positives'] ?? 0) + 1;
                $byDim[$dim][$k]['linked'] = ($byDim[$dim][$k]['linked'] ?? 0) + ($isLinked ? 1 : 0);
            }
        }
        sort($days);

        $dimRows = [];
        foreach ($byDim as $dim => $groups) {
            ksort($groups);
            foreach ($groups as $label => $g) {
                $dimRows[$dim][] = ['label' => $label, 'positives' => $g['positives'], 'linked' => $g['linked'], 'pct' => round($g['linked'] / $g['positives'] * 100, 1)];
            }
        }

        $pct = fn (int $n, int $d) => $d > 0 ? round($n / $d * 100, 1) : null;

        return [
            'steps' => [
                ['label' => 'Tested for HIV (all entry points)', 'count' => (int) $testedTotal, 'pct_of_previous' => null],
                ['label' => 'Tested positive', 'count' => $positives, 'pct_of_previous' => $pct($positives, (int) $testedTotal)],
                ['label' => 'Linked to ART / HIV care', 'count' => $linked, 'pct_of_previous' => $pct($linked, $positives)],
                ['label' => 'Viral load tested after diagnosis', 'count' => $vlTested, 'pct_of_previous' => $pct($vlTested, $linked)],
                ['label' => 'Virally suppressed', 'count' => $suppressed, 'pct_of_previous' => $pct($suppressed, $vlTested)],
            ],
            'linkage_pct' => $pct($linked, $positives),
            'median_days_to_art' => $days ? $days[intdiv(count($days), 2)] : null,
            'time_to_art' => array_map(fn ($k, $v) => ['label' => $k, 'count' => $v], array_keys($timeBuckets), $timeBuckets),
            'time_to_art_distribution' => $this->fiveNumberSummary($days),
            'linkage_by' => $dimRows,
            'note' => 'Linkage = an OI/ART registration or visit on/after the positive test (30-day tolerance for entry-date discrepancies), or ART initiation recorded in the HTS register. Positive = first positive result in the period across HTS, STI, PrEP, ANC and OI/ART registers.',
        ];
    }

    /**
     * Min/Q1/median/Q3/max of an already-sorted numeric array, for a boxPlot
     * chart — shows the real spread of days-to-linkage instead of collapsing
     * it to a single median or a handful of fixed buckets.
     */
    private function fiveNumberSummary(array $sorted): ?array
    {
        $n = count($sorted);
        if ($n === 0) return null;

        $quantile = function (float $q) use ($sorted, $n) {
            $pos = $q * ($n - 1);
            $lo = (int) floor($pos);
            $hi = (int) ceil($pos);

            return $sorted[$lo] + ($sorted[$hi] - $sorted[$lo]) * ($pos - $lo);
        };

        return [
            'min' => $sorted[0],
            'q1' => round($quantile(0.25), 1),
            'median' => round($quantile(0.5), 1),
            'q3' => round($quantile(0.75), 1),
            'max' => $sorted[$n - 1],
            'n' => $n,
        ];
    }

    private function prepCascade(): array
    {
        [$demog, $bind] = $this->demogSql();
        $prepr = $this->pivotSql(self::$P_ALL, [
            'reg_date' => 'prepr_date', 'screened' => 'prepr_screened', 'outcome' => 'prepr_screen_outcome',
            'visit_status' => 'prepr_visit_status', 'initiate' => 'prepr_prep_initiate', 'sti_screen' => 'prepr_sti_screening',
        ]);

        $r = DB::selectOne("
            WITH demog AS ({$demog}), p AS ({$prepr})
            SELECT COUNT(DISTINCT CASE WHEN p.screened = '1' THEN p.record END) AS screened,
                   COUNT(DISTINCT CASE WHEN p.screened = '1' AND p.outcome = 'E' THEN p.record END) AS eligible,
                   COUNT(DISTINCT CASE WHEN p.visit_status = 'N' OR p.initiate = '1' THEN p.record END) AS initiated,
                   COUNT(DISTINCT CASE WHEN p.visit_status = 'C' THEN p.record END) AS continuing,
                   COUNT(DISTINCT CASE WHEN p.visit_status = 'D' THEN p.record END) AS discontinued,
                   COUNT(DISTINCT CASE WHEN p.sti_screen = '1' THEN p.record END) AS sti_screened,
                   COUNT(DISTINCT p.record) AS registered
            FROM p JOIN demog d ON d.record = p.record
            WHERE {$this->periodCond('p.reg_date')} AND {$this->ageCond('p.reg_date')}
        ", $bind);

        $pct = fn ($n, $d) => $d > 0 ? round($n / $d * 100, 1) : null;

        // Initiation is not a strict subset of "screened" in the register
        // (screening is often skipped for clients initiated directly), so
        // each step is shown as a share of register entries, not a funnel.
        return [
            'steps' => [
                ['label' => 'PrEP register entries (clients)', 'count' => (int) $r->registered, 'pct_of_previous' => null],
                ['label' => 'Screened for eligibility', 'count' => (int) $r->screened, 'pct_of_previous' => $pct($r->screened, $r->registered)],
                ['label' => 'Screened eligible', 'count' => (int) $r->eligible, 'pct_of_previous' => $pct($r->eligible, $r->registered)],
                ['label' => 'Initiated', 'count' => (int) $r->initiated, 'pct_of_previous' => $pct($r->initiated, $r->registered)],
            ],
            'steps_base' => 'register entries',
            'continuing' => (int) $r->continuing,
            'discontinued' => (int) $r->discontinued,
            'sti_screened_pct' => $pct($r->sti_screened, $r->registered),
            'note' => 'Shares are of all PrEP register entries in the period; initiation can be recorded without a documented eligibility screen.',
        ];
    }

    private function ancContinuum(array $profiles): array
    {
        [$demog, $bind] = $this->demogSql();
        $ancr = $this->pivotSql(self::$P_FCH, ['reg_date' => 'ancr_date', 'first_booking' => 'ancr_first_booking', 'hiv_prior' => 'ancr_hiv_prior']);
        $pncr = $this->pivotSql(self::$P_FCH, ['reg_date' => 'pncr_date', 'hiv_post' => 'pncr_hiv_status_post', 'on_art' => 'pncr_hiv_status_art', 'place' => 'pncr_place_of_delivery']);

        $r = DB::selectOne("
            WITH demog AS ({$demog}), a AS ({$ancr}), b AS ({$pncr})
            SELECT
              (SELECT COUNT(DISTINCT a.record) FROM a JOIN demog d ON d.record = a.record
                WHERE a.first_booking = '1' AND {$this->periodCond('a.reg_date')} AND {$this->ageCond('a.reg_date')}) AS bookings,
              (SELECT COUNT(DISTINCT a.record) FROM a JOIN demog d ON d.record = a.record
                WHERE a.first_booking = '1' AND a.hiv_prior IN ('0','1') AND {$this->periodCond('a.reg_date')} AND {$this->ageCond('a.reg_date')}) AS bookings_hiv_known,
              (SELECT COUNT(DISTINCT b.record) FROM b JOIN demog d ON d.record = b.record
                WHERE {$this->periodCond('b.reg_date')} AND {$this->ageCond('b.reg_date')}) AS deliveries,
              (SELECT COUNT(DISTINCT b.record) FROM b JOIN demog d ON d.record = b.record
                WHERE b.place = '1' AND {$this->periodCond('b.reg_date')} AND {$this->ageCond('b.reg_date')}) AS institutional,
              (SELECT COUNT(DISTINCT b.record) FROM b JOIN demog d ON d.record = b.record
                WHERE b.hiv_post = 'P' AND {$this->periodCond('b.reg_date')} AND {$this->ageCond('b.reg_date')}) AS hiv_pos_mothers,
              (SELECT COUNT(DISTINCT b.record) FROM b JOIN demog d ON d.record = b.record
                WHERE b.hiv_post = 'P' AND b.on_art = '1' AND {$this->periodCond('b.reg_date')} AND {$this->ageCond('b.reg_date')}) AS hiv_pos_on_art
        ", $bind);

        // Service-family presence from the profile (ANC follow-up, PNC, FP after delivery)
        $anc = array_filter($profiles, fn ($p) => isset($p['families']['ANC']));
        $ancWithPnc = count(array_filter($anc, fn ($p) => isset($p['families']['PNC'])));
        $pnc = array_filter($profiles, fn ($p) => isset($p['families']['PNC']));
        $pncWithFp = count(array_filter($pnc, fn ($p) => isset($p['families']['Family planning'])));
        $ancWithHts = count(array_filter($anc, fn ($p) => isset($p['families']['HIV testing'])));

        $pct = fn ($n, $d) => $d > 0 ? round($n / $d * 100, 1) : null;

        return [
            'bookings' => (int) $r->bookings,
            'bookings_hiv_known_pct' => $pct($r->bookings_hiv_known, $r->bookings),
            'deliveries' => (int) $r->deliveries,
            'institutional_pct' => $pct($r->institutional, $r->deliveries),
            'hiv_pos_mothers' => (int) $r->hiv_pos_mothers,
            'hiv_pos_on_art_pct' => $pct($r->hiv_pos_on_art, $r->hiv_pos_mothers),
            'anc_clients' => count($anc),
            'anc_to_pnc_pct' => $pct($ancWithPnc, count($anc)),
            'anc_with_hts_pct' => $pct($ancWithHts, count($anc)),
            'pnc_clients' => count($pnc),
            'pnc_to_fp_pct' => $pct($pncWithFp, count($pnc)),
        ];
    }

    private function mhPathway(): array
    {
        [$demog, $bind] = $this->demogSql();
        $mh = $this->pivotSql(self::$P_ALL, [
            'screened' => 'mh_screening_tools', 'result' => 'mh_screening_results',
            'managed' => 'mh_management_outcome', 'substance' => 'mh_substance_identified',
        ]);
        $r = DB::selectOne("
            WITH demog AS ({$demog}), m AS ({$mh})
            SELECT COUNT(DISTINCT CASE WHEN m.screened = '1' THEN m.record END) AS screened,
                   COUNT(DISTINCT CASE WHEN m.result = 'P' THEN m.record END) AS positive,
                   COUNT(DISTINCT CASE WHEN m.result = 'P' AND m.managed IN ('R','M','B') THEN m.record END) AS positive_managed,
                   COUNT(DISTINCT CASE WHEN m.managed = 'R' THEN m.record END) AS referred,
                   COUNT(DISTINCT CASE WHEN m.managed = 'M' THEN m.record END) AS managed_only,
                   COUNT(DISTINCT CASE WHEN m.managed = 'B' THEN m.record END) AS both_rm,
                   COUNT(DISTINCT CASE WHEN m.substance = '1' THEN m.record END) AS substance
            FROM m JOIN demog d ON d.record = m.record
            WHERE {$this->ageAtPeriodEnd()}
        ", $bind);

        $pct = fn ($n, $d) => $d > 0 ? round($n / $d * 100, 1) : null;

        return [
            'screened' => (int) $r->screened,
            'positive' => (int) $r->positive,
            'positive_pct' => $pct($r->positive, $r->screened),
            'positive_managed_pct' => $pct($r->positive_managed, $r->positive),
            'outcomes' => [
                ['label' => 'Referred', 'count' => (int) $r->referred],
                ['label' => 'Managed on site', 'count' => (int) $r->managed_only],
                ['label' => 'Referred and managed', 'count' => (int) $r->both_rm],
            ],
            'substance' => (int) $r->substance,
            'note' => 'The mental-health instrument has no date field: these figures are all-time for clients aged 10-19 at the period end.',
        ];
    }
}
