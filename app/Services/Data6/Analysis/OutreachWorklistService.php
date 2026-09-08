<?php

namespace App\Services\Data6\Analysis;

use App\Services\Data6\QueryFragments;
use Illuminate\Support\Facades\DB;

/**
 * Pro+ outreach worklist: who specifically needs to be chased up right now,
 * across every program that actually records a signal for it. Each program
 * keeps its own real rule rather than forcing one shape onto all of them:
 *
 * - OI/ART: `art_next_review_date` + 28 days overdue - the exact rule
 *   AHP008/AHP010 and ArtCascadeAnalysis::cohortOutcomes() already use.
 * - PrEP: `prep_next_visit_date` + 28 days overdue (present in the register,
 *   never wired into any Data6 service before this), or a direct
 *   `prep_follow_up_status = 'LTFU'` clinician-recorded flag.
 * - PNC (mother/baby): no next-appointment date exists in the register at
 *   all - flagged only by the clinician's own recorded follow-up status
 *   ("Missed appointment" / "Lost to follow-up" / "Infant lost to
 *   follow-up"), never a fabricated time-since-last-visit heuristic.
 * - ANC is deliberately excluded: it has neither a next-appointment date
 *   nor a general status field (the only status field is gated to a narrow
 *   ART-initiated-ANC subset), so there's no reliable signal to compute
 *   this from - shown as a note on the page rather than guessed at.
 */
class OutreachWorklistService
{
    use QueryFragments;

    private string $to;

    private ?string $district = null;

    private ?string $facility = null;

    private ?string $gender = null;

    private ?int $ageLo = 10;

    private ?int $ageHi = 19;

    public function compute(string $asOf): array
    {
        $this->to = $asOf;
        $this->materializeDemog();

        return [
            'as_of' => $asOf,
            'art' => $this->artOverdue(),
            'prep' => $this->prepOverdue(),
            'pnc_mother' => $this->pncMotherOverdue(),
            'pnc_baby' => $this->pncBabyOverdue(),
        ];
    }

    /** Same materialize-once pattern as ArtCascadeAnalysis - four separate
     *  program queries below would otherwise each re-pay demogSql()'s cost. */
    private function materializeDemog(): void
    {
        [$demogSql, $bind] = $this->demogSql();
        DB::statement('DROP TEMPORARY TABLE IF EXISTS demog');
        DB::statement("CREATE TEMPORARY TABLE demog AS {$demogSql}", $bind);
        DB::statement('ALTER TABLE demog ADD INDEX (record)');
    }

    private function artOverdue(): array
    {
        $artr = $this->pivotSql(self::$P_ART, ['access' => 'artr_access']);
        $art = $this->pivotSql(self::$P_ART, [
            'visit_date' => 'art_review_date', 'next_visit' => 'art_next_review_date', 'outcome' => 'art_final_outcome',
        ]);

        // Active/LTFU conditions copied verbatim from
        // ArtCascadeAnalysis::cohortOutcomes() / IndicatorService::artIndicators()
        // so this list is guaranteed consistent with AHP008/AHP010 by
        // construction, not just on today's data.
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
            SELECT d.record, d.facility, d.district, l.visit_date AS last_visit, l.next_visit AS next_appointment,
                   DATEDIFF('{$this->to}', l.next_visit) AS days_overdue,
                   'Next appointment overdue' AS reason
            FROM cohort c
            JOIN demog d ON d.record = c.record
            JOIN latest l ON l.record = c.record AND l.rn = 1
            WHERE {$this->ageAtPeriodEnd()}
              AND (l.outcome IS NULL OR l.outcome NOT IN ('4', '5', '6'))
              AND l.next_visit REGEXP '".self::$DATE_RE."'
              AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) < '{$this->to}'
            ORDER BY days_overdue DESC
        ");

        return $this->mapRows($rows, 'OI/ART');
    }

    private function prepOverdue(): array
    {
        // PrEP is a shared instrument mirrored across all three projects
        // (self::$P_ALL), same as encountersSql() treats it. Its own
        // dictionary-documented access condition is `= 'Y'` - unlike ART's
        // `= '1'` - each program's real coding is used as-is, not copied.
        $prepr = $this->pivotSql(self::$P_ALL, ['access' => 'prepr_access']);
        $prep = $this->pivotSql(self::$P_ALL, [
            'visit_date' => 'prep_visit_date', 'next_visit' => 'prep_next_visit_date', 'status' => 'prep_follow_up_status',
        ]);

        $rows = DB::select("
            WITH preprp AS ({$prepr}), prepp AS ({$prep}),
            cohort AS (
                SELECT DISTINCT record FROM preprp WHERE access = 'Y'
                UNION
                SELECT DISTINCT record FROM prepp
            ),
            latest AS (
                SELECT p.*, ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date DESC) AS rn
                FROM prepp p
                WHERE p.visit_date REGEXP '".self::$DATE_RE."' AND p.visit_date <= '{$this->to}'
            )
            SELECT d.record, d.facility, d.district, l.visit_date AS last_visit, l.next_visit AS next_appointment,
                   CASE WHEN l.status = 'LTFU' THEN DATEDIFF('{$this->to}', l.visit_date)
                        ELSE DATEDIFF('{$this->to}', l.next_visit) END AS days_overdue,
                   CASE WHEN l.status = 'LTFU' THEN 'Recorded as lost to follow-up'
                        ELSE 'Next appointment overdue' END AS reason
            FROM cohort c
            JOIN demog d ON d.record = c.record
            JOIN latest l ON l.record = c.record AND l.rn = 1
            WHERE {$this->ageAtPeriodEnd()}
              AND (
                    l.status = 'LTFU'
                    OR (
                        (l.status IS NULL OR l.status NOT IN ('OO', 'WTH', 'TO', 'D'))
                        AND l.next_visit REGEXP '".self::$DATE_RE."'
                        AND DATE_ADD(l.next_visit, INTERVAL 28 DAY) < '{$this->to}'
                    )
                  )
            ORDER BY days_overdue DESC
        ");

        return $this->mapRows($rows, 'PrEP');
    }

    private function pncMotherOverdue(): array
    {
        $pncr = $this->pivotSql(self::$P_FCH, ['access' => 'pncr_access']);
        $pncm = $this->pivotSql(self::$P_FCH, [
            'visit_date' => 'pncm_visit_date', 'status' => 'pncm_mother_follow_up',
        ]);

        // No next-appointment field exists for PNC - flagged purely by the
        // clinician's own recorded status at the last visit, never a
        // fabricated time-since-last-visit guess.
        $rows = DB::select("
            WITH pncrp AS ({$pncr}), pncmp AS ({$pncm}),
            cohort AS (
                SELECT DISTINCT record FROM pncrp WHERE access = 'Y'
                UNION
                SELECT DISTINCT record FROM pncmp
            ),
            latest AS (
                SELECT p.*, ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date DESC) AS rn
                FROM pncmp p
                WHERE p.visit_date REGEXP '".self::$DATE_RE."' AND p.visit_date <= '{$this->to}'
            )
            SELECT d.record, d.facility, d.district, l.visit_date AS last_visit, NULL AS next_appointment,
                   DATEDIFF('{$this->to}', l.visit_date) AS days_overdue,
                   CASE WHEN l.status = '4' THEN 'Lost to follow-up' ELSE 'Missed appointment' END AS reason
            FROM cohort c
            JOIN demog d ON d.record = c.record
            JOIN latest l ON l.record = c.record AND l.rn = 1
            WHERE {$this->ageAtPeriodEnd()}
              AND l.status IN ('3', '4')
            ORDER BY days_overdue DESC
        ");

        return $this->mapRows($rows, 'PNC (mother)');
    }

    private function pncBabyOverdue(): array
    {
        $pncr = $this->pivotSql(self::$P_FCH, ['access' => 'pncr_access']);
        $pncb = $this->pivotSql(self::$P_FCH, [
            'visit_date' => 'pncb_visit_date', 'status' => 'pncb_infant_follow_ups',
        ]);

        $rows = DB::select("
            WITH pncrp AS ({$pncr}), pncbp AS ({$pncb}),
            cohort AS (
                SELECT DISTINCT record FROM pncrp WHERE access = 'Y'
                UNION
                SELECT DISTINCT record FROM pncbp
            ),
            latest AS (
                SELECT p.*, ROW_NUMBER() OVER (PARTITION BY p.record ORDER BY p.visit_date DESC) AS rn
                FROM pncbp p
                WHERE p.visit_date REGEXP '".self::$DATE_RE."' AND p.visit_date <= '{$this->to}'
            )
            SELECT d.record, d.facility, d.district, l.visit_date AS last_visit, NULL AS next_appointment,
                   DATEDIFF('{$this->to}', l.visit_date) AS days_overdue,
                   'Infant lost to follow-up' AS reason
            FROM cohort c
            JOIN demog d ON d.record = c.record
            JOIN latest l ON l.record = c.record AND l.rn = 1
            WHERE {$this->ageAtPeriodEnd()}
              AND l.status = '5'
            ORDER BY days_overdue DESC
        ");

        return $this->mapRows($rows, 'PNC (baby)');
    }

    private function mapRows(array $rows, string $program): array
    {
        return array_map(fn ($r) => [
            'record' => $r->record,
            'program' => $program,
            'reason' => $r->reason,
            'facility' => $r->facility,
            'district' => $r->district,
            'last_visit' => $r->last_visit,
            'next_appointment' => $r->next_appointment,
            'days_overdue' => (int) $r->days_overdue,
        ], $rows);
    }
}
