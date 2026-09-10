<?php

namespace App\Services\Data6\Analysis;

use App\Services\Data6\QueryFragments;
use Illuminate\Support\Facades\DB;

/**
 * Indicator-specific analysis for the ART cascade group (AHP007-017):
 * a cohort-shaped view that no single indicator's own chart can produce,
 * because AHP008/010/012/013 are each independent period-scoped counts.
 *
 * 1. Cohort outcome breakdown - every client who ever touched ART care,
 *    classified into exactly one status as of the period end. Active/LTFU
 *    use the SAME conditions as AHP008/AHP010 (so those counts must match
 *    exactly - verified in tests), extended with two buckets AHP008/010
 *    silently drop: Opted out and No follow-up recorded.
 * 2. 12-month retention across the last 6 eligible initiation cohorts,
 *    extending AHP009's single-point formula into a trend.
 * 3. VL testing coverage among the Active cohort - a ratio with a real
 *    denominator, unlike AHP014's raw period count.
 */
class ArtCascadeAnalysis
{
    use QueryFragments;

    private string $to;

    private ?string $district = null;

    private ?string $facility = null;

    private ?string $gender = null;

    private ?int $ageLo = 10;

    private ?int $ageHi = 19;

    public function compute(string $to): array
    {
        $this->to = $to;
        $this->materializeDemog();

        return [
            'cohort_outcomes' => $this->cohortOutcomes(),
            'retention_trend' => $this->retentionTrend(),
            'vl_coverage' => $this->vlCoverage(),
        ];
    }

    /**
     * demogSql() costs ~1s to materialize (a full scan of redcap_data6 is
     * cheaper than an index dive for this shape - measured, not assumed).
     * Each of the three sub-analyses used to embed it as its own CTE, so
     * MySQL re-paid that cost on every one of the three queries. Building
     * it once into a real temporary table and having every query below
     * join against that instead cuts three ~1s scans down to one.
     */
    private function materializeDemog(): void
    {
        [$demogSql, $bind] = $this->demogSql();
        DB::statement('DROP TEMPORARY TABLE IF EXISTS demog');
        DB::statement("CREATE TEMPORARY TABLE demog AS {$demogSql}", $bind);
        DB::statement('ALTER TABLE demog ADD INDEX (record)');
    }

    // ------------------------------------------------------------------
    // 1. Cohort outcome breakdown
    // ------------------------------------------------------------------

    private function cohortOutcomes(): array
    {
        $artr = $this->pivotSql(self::$P_ART, ['referred' => 'artr_referred', 'reg_date' => 'artr_registration_date', 'access' => 'artr_access']);
        $art = $this->pivotSql(self::$P_ART, [
            'visit_date' => 'art_review_date', 'next_visit' => 'art_next_review_date', 'outcome' => 'art_final_outcome',
        ]);

        $rows = DB::select("
            WITH artrp AS ({$artr}), artp AS ({$art}),
            cohort AS (
                SELECT DISTINCT record FROM artrp WHERE access = '1'
                UNION
                SELECT DISTINCT record FROM artp
            ),
            latest AS (
                SELECT p.*, ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date DESC) AS rn
                FROM artp p
                WHERE p.visit_date REGEXP '".self::$DATE_RE."' AND p.visit_date <= '{$this->to}'
            )
            SELECT
              -- Active/LTFU conditions are copied verbatim from
              -- IndicatorService::artIndicators() (tx_curr/ltfu) so these two
              -- buckets are guaranteed to equal AHP008/AHP010 by construction,
              -- not just on today's data. Anything satisfying neither -
              -- outcome recorded as something other than Died/TO/Opted-out
              -- but with no valid/current next-appointment date - falls into
              -- one honestly-labelled residual bucket rather than being
              -- guessed at.
              CASE
                WHEN l.record IS NULL THEN 'No follow-up recorded'
                WHEN l.outcome = '5' THEN 'Died'
                WHEN l.outcome = '4' THEN 'Transferred out'
                WHEN l.outcome = '6' THEN 'Opted out'
                WHEN (l.outcome IS NULL OR l.outcome NOT IN ('3','4','5','6'))
                     AND l.next_visit REGEXP '".self::$DATE_RE."'
                     AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) >= '{$this->to}' THEN 'Active'
                WHEN (l.outcome IS NULL OR l.outcome NOT IN ('4','5'))
                     AND l.next_visit REGEXP '".self::$DATE_RE."'
                     AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) < '{$this->to}' THEN 'LTFU'
                ELSE 'Other / status unclear'
              END AS bucket
            FROM cohort c
            JOIN demog d ON d.record = c.record
            LEFT JOIN latest l ON l.record = c.record AND l.rn = 1
            WHERE {$this->ageAtPeriodEnd()}
        ");

        $order = ['Active', 'LTFU', 'Transferred out', 'Died', 'Opted out', 'Other / status unclear', 'No follow-up recorded'];
        $counts = array_fill_keys($order, 0);
        foreach ($rows as $row) {
            $counts[$row->bucket] = ($counts[$row->bucket] ?? 0) + 1;
        }
        $total = array_sum($counts);

        return [
            'total' => $total,
            'by' => array_values(array_map(
                fn ($label) => ['label' => $label, 'value' => $counts[$label], 'pct' => $total > 0 ? round($counts[$label] / $total * 100, 1) : 0],
                $order
            )),
        ];
    }

    // ------------------------------------------------------------------
    // 2. Retention trend - 6 point-in-time snapshots of AHP009's own rule
    // ------------------------------------------------------------------

    /**
     * 2026-09 client-supplied methodology: AHP009 is no longer an
     * initiation-cohort formula, it's a per-record snapshot (see
     * IndicatorService::artIndicators()'s art_retention block, which this
     * must stay consistent with). A "trend" here means re-running that same
     * snapshot rule as of 6 different dates - the last day of each of the
     * 5 months before `to`, plus `to` itself - not 6 different initiation
     * cohorts. `cohort_size`/`retained` keep their field names (the Vue
     * chart reads them unchanged) but now mean "eligible as of that date" /
     * "retained as of that date".
     */
    private function retentionTrend(): array
    {
        $artr = $this->pivotSql(self::$P_ART, ['reg_date' => 'artr_registration_date']);
        $art = $this->pivotSql(self::$P_ART, [
            'visit_date' => 'art_review_date', 'next_visit' => 'art_next_review_date', 'outcome' => 'art_final_outcome',
        ]);

        $points = [];
        for ($i = 5; $i >= 1; $i--) {
            $points[] = date('Y-m-t', strtotime("{$this->to} -{$i} months"));
        }
        $points[] = $this->to;

        return array_map(function (string $asOf) use ($artr, $art) {
            $row = DB::selectOne("
                WITH reg AS (
                    SELECT record, MIN(reg_date) AS reg_date
                    FROM ({$artr}) x WHERE reg_date REGEXP '".self::$DATE_RE."'
                    GROUP BY record
                ),
                visits AS (
                    SELECT p.*,
                           ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date) AS rn,
                           COUNT(*) OVER (PARTITION BY p.record) AS n,
                           LAG(p.next_visit) OVER (PARTITION BY p.record ORDER BY p.visit_date) AS prev_next_visit
                    FROM ({$art}) p
                    WHERE p.visit_date REGEXP '".self::$DATE_RE."' AND p.visit_date <= '{$asOf}'
                ),
                latest AS (SELECT * FROM visits WHERE rn = n)
                SELECT
                  COUNT(*) AS cohort,
                  COALESCE(SUM(CASE WHEN l.outcome = '1'
                            AND NOT (l.n > 1 AND l.prev_next_visit REGEXP '".self::$DATE_RE."'
                                     AND DATEDIFF(l.visit_date, l.prev_next_visit) >= 28)
                           THEN 1 ELSE 0 END), 0) AS retained
                FROM latest l
                JOIN reg r ON r.record = l.record
                JOIN demog d ON d.record = l.record
                WHERE DATEDIFF(l.visit_date, r.reg_date) >= 365 AND {$this->ageCond("'{$asOf}'")}
            ");

            return [
                'label' => date('Y-m', strtotime($asOf)),
                'cohort_size' => (int) $row->cohort,
                'retained' => (int) $row->retained,
                'pct' => $row->cohort > 0 ? round($row->retained / $row->cohort * 100, 1) : null,
            ];
        }, $points);
    }

    // ------------------------------------------------------------------
    // 3. VL testing coverage among the Active cohort
    // ------------------------------------------------------------------

    private function vlCoverage(): array
    {
        $art = $this->pivotSql(self::$P_ART, [
            'visit_date' => 'art_review_date', 'next_visit' => 'art_next_review_date', 'outcome' => 'art_final_outcome',
            'vl_done' => 'art_viral_load', 'vl_date' => 'art_vl_collect_date',
        ]);
        $trailingStart = date('Y-m-d', strtotime($this->to.' -12 months'));

        $row = DB::selectOne("
            WITH artp AS ({$art}),
            latest AS (
                SELECT p.*, ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date DESC) AS rn
                FROM artp p WHERE p.visit_date REGEXP '".self::$DATE_RE."' AND p.visit_date <= '{$this->to}'
            ),
            active AS (
                SELECT l.record FROM latest l
                JOIN demog d ON d.record = l.record
                WHERE l.rn = 1
                  AND (l.outcome IS NULL OR l.outcome NOT IN ('3','4','5','6'))
                  AND l.next_visit REGEXP '".self::$DATE_RE."'
                  AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) >= '{$this->to}'
                  AND {$this->ageAtPeriodEnd()}
            )
            SELECT
              (SELECT COUNT(*) FROM active) AS active_total,
              (SELECT COUNT(DISTINCT a.record) FROM active a
                 JOIN artp v ON v.record = a.record
                WHERE v.vl_done = '1'
                  AND COALESCE(NULLIF(v.vl_date, ''), v.visit_date) BETWEEN '{$trailingStart}' AND '{$this->to}'
              ) AS tested_12mo
        ");

        $activeTotal = (int) $row->active_total;
        $tested = (int) $row->tested_12mo;

        return [
            'active_total' => $activeTotal,
            'tested_12mo' => $tested,
            'pct' => $activeTotal > 0 ? round($tested / $activeTotal * 100, 1) : null,
        ];
    }
}
