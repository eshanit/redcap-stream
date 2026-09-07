<?php

namespace App\Services\Data6\Analysis;

use App\Services\Data6\QueryFragments;
use Illuminate\Support\Facades\DB;

/**
 * Indicator-specific analysis for AHP004/005/006/007: those indicators
 * read the HTS register only, but a client can be tested for HIV - or
 * started on ART - through the STI register, PrEP follow-up, ANC booking,
 * or the OI/ART register itself, without that ever being logged in HTS.
 *
 * This computes the reconciliation gap: clients with evidence of testing
 * (or ART initiation) elsewhere who have no matching HTS entry in the same
 * period, so the M&E officer can see who specifically is missing from the
 * register - not just how many.
 *
 * Deliberately period-scoped on both sides (evidence AND the HTS check):
 * this answers "of the tests/initiations I have other evidence of THIS
 * period, how many also got logged in HTS this period" - what a routine
 * reconciliation needs, not a lifetime check.
 */
class HtsReconciliationAnalysis
{
    use QueryFragments;

    private string $from;

    private string $to;

    private ?string $district = null;

    private ?string $facility = null;

    private ?string $gender = null;

    private ?int $ageLo = 10;

    private ?int $ageHi = 19;

    public function compute(string $from, string $to): array
    {
        $this->from = $from;
        $this->to = $to;
        $this->materializeDemog();

        return [
            'hiv_testing' => $this->hivTestingGap(),
            'art_initiation' => $this->artInitiationGap(),
        ];
    }

    /**
     * demogSql() costs ~1s to materialize (a full scan of redcap_data6 is
     * cheaper than an index dive for this shape - measured, not assumed).
     * Both gap queries below used to embed it as their own CTE, so MySQL
     * re-paid that cost on each one. Building it once into a real
     * temporary table and joining against that instead halves the cost.
     */
    private function materializeDemog(): void
    {
        [$demogSql, $bind] = $this->demogSql();
        DB::statement('DROP TEMPORARY TABLE IF EXISTS demog');
        DB::statement("CREATE TEMPORARY TABLE demog AS {$demogSql}", $bind);
        DB::statement('ALTER TABLE demog ADD INDEX (record)');
    }

    // ------------------------------------------------------------------
    // AHP004/005/006: testing evidenced elsewhere but missing from HTS
    // ------------------------------------------------------------------

    private function hivTestingGap(): array
    {
        $hts = $this->pivotSql(self::$P_ALL, ['tested' => 'hts_tested', 'test_date' => 'hts_hiv_date']);
        $sti = $this->pivotSql(self::$P_ALL, ['tested' => 'sti_hiv_test', 'test_date' => 'sti_visit_date', 'result' => 'sti_hiv_test_result']);
        $prep = $this->pivotSql(self::$P_ALL, ['tested' => 'prep_hiv_test', 'test_date' => 'prep_visit_date', 'result' => 'prep_hiv_test_results']);
        $anc = $this->pivotSql(self::$P_FCH, ['result' => 'anc_hiv_test_results', 'test_date' => 'anc_date']);
        $artr = $this->pivotSql(self::$P_ART, ['first_test' => 'artr_first_hiv_test']);

        $rows = DB::select("
            WITH evidence AS (
                SELECT record, test_date, result, 'STI register' AS source FROM ({$sti}) t WHERE t.tested = '1'
                UNION ALL
                SELECT record, test_date, result, 'PrEP follow-up' FROM ({$prep}) t WHERE t.tested = '1'
                UNION ALL
                SELECT record, test_date, result, 'ANC' FROM ({$anc}) t WHERE t.result IN ('P', 'N')
                UNION ALL
                SELECT record, first_test AS test_date, 'P' AS result, 'OI/ART register' FROM ({$artr}) t
                WHERE t.first_test REGEXP '".self::$DATE_RE."'
            ),
            evidence_period AS (
                SELECT e.record, e.test_date, e.result, e.source,
                       ROW_NUMBER() OVER (PARTITION BY e.record ORDER BY e.test_date) AS rn
                FROM evidence e
                JOIN demog d ON d.record = e.record
                WHERE {$this->periodCond('e.test_date')} AND {$this->ageCond('e.test_date')}
            ),
            hts_period AS (
                SELECT DISTINCT record FROM ({$hts}) h
                WHERE h.tested = '1' AND {$this->periodCond('h.test_date')}
            )
            SELECT ep.record, ep.test_date, ep.result, ep.source,
                   COALESCE(NULLIF(d.facility, ''), 'Unknown') AS facility,
                   COALESCE(NULLIF(d.district, ''), 'Unknown') AS district,
                   CASE WHEN h.record IS NULL THEN 1 ELSE 0 END AS is_gap
            FROM evidence_period ep
            JOIN demog d ON d.record = ep.record
            LEFT JOIN hts_period h ON h.record = ep.record
            WHERE ep.rn = 1
        ");

        return $this->summarize($rows, includeResult: true);
    }

    // ------------------------------------------------------------------
    // AHP007: ART initiation evidenced in the ART register but missing
    // from the HTS register's hts_art_init flag
    // ------------------------------------------------------------------

    private function artInitiationGap(): array
    {
        $hts = $this->pivotSql(self::$P_ALL, ['art_init' => 'hts_art_init', 'test_date' => 'hts_hiv_date']);
        $art = $this->pivotSql(self::$P_ART, ['arv_status' => 'art_arv_status', 'visit_date' => 'art_review_date']);

        $rows = DB::select("
            WITH evidence AS (
                SELECT record, visit_date AS test_date, 'ART follow-up (Start ARV)' AS source
                FROM ({$art}) p WHERE p.arv_status = '2'
            ),
            evidence_period AS (
                SELECT e.record, e.test_date, e.source,
                       ROW_NUMBER() OVER (PARTITION BY e.record ORDER BY e.test_date) AS rn
                FROM evidence e
                JOIN demog d ON d.record = e.record
                WHERE {$this->periodCond('e.test_date')} AND {$this->ageCond('e.test_date')}
            ),
            hts_period AS (
                SELECT DISTINCT record FROM ({$hts}) h
                WHERE h.art_init = 'Y' AND {$this->periodCond('h.test_date')}
            )
            SELECT ep.record, ep.test_date, ep.source,
                   COALESCE(NULLIF(d.facility, ''), 'Unknown') AS facility,
                   COALESCE(NULLIF(d.district, ''), 'Unknown') AS district,
                   CASE WHEN h.record IS NULL THEN 1 ELSE 0 END AS is_gap
            FROM evidence_period ep
            JOIN demog d ON d.record = ep.record
            LEFT JOIN hts_period h ON h.record = ep.record
            WHERE ep.rn = 1
        ");

        return $this->summarize($rows, includeResult: false);
    }

    private function summarize(array $rows, bool $includeResult): array
    {
        $evidencedElsewhere = count($rows);
        $gapRows = array_filter($rows, fn ($r) => (int) $r->is_gap === 1);
        $gap = count($gapRows);
        $inHts = $evidencedElsewhere - $gap;

        $bySource = [];
        $byFacility = [];
        $byDistrict = [];
        foreach ($gapRows as $r) {
            $bySource[$r->source] = ($bySource[$r->source] ?? 0) + 1;
            $byFacility[$r->facility] = ($byFacility[$r->facility] ?? 0) + 1;
            $byDistrict[$r->district] = ($byDistrict[$r->district] ?? 0) + 1;
        }
        arsort($bySource);
        ksort($byFacility);
        ksort($byDistrict);

        $records = array_map(function ($r) use ($includeResult) {
            $record = [
                'record' => $r->record,
                'source' => $r->source,
                'date' => $r->test_date,
                'facility' => $r->facility,
                'district' => $r->district,
            ];
            if ($includeResult) {
                $record['result'] = $r->result;
            }

            return $record;
        }, $gapRows);

        return [
            'evidenced_elsewhere' => $evidencedElsewhere,
            'in_hts' => $inHts,
            'gap' => $gap,
            'gap_pct' => $evidencedElsewhere > 0 ? round($gap / $evidencedElsewhere * 100, 1) : null,
            'by_source' => array_map(fn ($k, $v) => ['label' => $k, 'value' => $v], array_keys($bySource), array_values($bySource)),
            'by_facility' => array_map(fn ($k, $v) => ['label' => $k, 'value' => $v], array_keys($byFacility), array_values($byFacility)),
            'by_district' => array_map(fn ($k, $v) => ['label' => $k, 'value' => $v], array_keys($byDistrict), array_values($byDistrict)),
            'records' => array_values($records),
        ];
    }
}
