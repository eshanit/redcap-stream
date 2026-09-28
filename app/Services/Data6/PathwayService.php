<?php

namespace App\Services\Data6;

use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

/**
 * Patient pathways: how adolescents move from their first service to the
 * next ones, e.g. "of those who started with STI, 45% later reached HIV
 * testing (median 30 days)".
 *
 * Unlike InsightsService (a snapshot of visits inside the period), this is a
 * cohort analysis:
 *  - Cohort = clients whose FIRST-EVER dated encounter (any service, all
 *    time) falls in the entry period, aged within the band at that date.
 *  - Each client is followed for a fixed window (3/6/12 months) from entry.
 *    Clients who entered too recently to have had the full window are
 *    excluded and counted as "too recent" - otherwise late entrants, who
 *    haven't had time to move on yet, would drag every rate down.
 *  - A pathway is the ORDER in which each service was first entered. Services
 *    first entered on the same day become one combined step ("ANC + HIV
 *    testing"): the data can't say which came first, so we don't claim to.
 *  - Dateless instruments (mental health, health education, counselling) and
 *    the OI/ART baseline can't be placed in a sequence and are left out.
 *  - Groups smaller than MIN_CELL are suppressed / folded into "Other":
 *    small numbers make unstable percentages and risk identifying clients.
 */
class PathwayService
{
    use QueryFragments;

    public const WINDOWS = [3, 6, 12];

    /** Services only girls can receive: boys are excluded from their denominators. */
    private const FEMALE_ONLY = ['ANC', 'PNC', 'Family planning'];

    private const MIN_CELL = 5;

    /** Steps shown in the flow diagram; longer paths are cut after this. */
    private const FLOW_STEPS = 4;

    private string $from;

    private string $to;

    private ?string $district;

    private ?string $facility;

    private ?string $gender;

    private ?int $ageLo;

    private ?int $ageHi;

    private int $window;

    public function compute(array $filters): array
    {
        $this->setFilters($filters);
        $asOf = $this->asOf();
        [$clients, $tooRecent, $cohort] = $this->clients($asOf);

        // Two different things hide inside "used more than one service":
        // several services on the same day (one step, e.g. tested for HIV
        // during an ANC visit) versus genuinely moving on to a new service
        // later (two or more steps). Only the second is a pathway.
        $movedOn = array_filter($clients, fn ($c) => count($c['steps']) > 1);
        $sameDayOnly = array_filter($clients, fn ($c) => count($c['steps']) === 1 && count($c['families']) > 1);
        $n = count($clients);

        return [
            'as_of' => $asOf,
            'window_months' => $this->window,
            'min_cell' => self::MIN_CELL,
            'summary' => [
                'cohort' => $cohort,
                'too_recent' => $tooRecent,
                'followed' => $n,
                'moved_on' => count($movedOn),
                'moved_on_pct' => $n ? round(count($movedOn) / $n * 100, 1) : 0,
                'same_day_only' => count($sameDayOnly),
                'same_day_only_pct' => $n ? round(count($sameDayOnly) / $n * 100, 1) : 0,
                'distinct_paths' => count(array_unique(array_map(fn ($c) => implode(' > ', $c['steps']), $movedOn))),
            ],
            'progression' => $this->progression($clients),
            'flow' => $this->flow($movedOn),
            'paths' => $this->paths($movedOn),
            'same_day' => $this->sameDay($clients),
        ];
    }

    /**
     * The adolescents behind one number on the page - a pathway, a same-day
     * combination, a where-next cell or a flow band - built from exactly the
     * same client set as compute(), so the list always matches the count.
     *
     * $selector: ['kind' => 'path'|'same_day', 'key' => label as shown]
     *          | ['kind' => 'cell', 'start' => service, 'reached' => service]
     *          | ['kind' => 'link', 'source' => 'stage|label', 'target' => 'stage|label']
     */
    public function members(array $filters, array $selector): array
    {
        $this->setFilters($filters);
        [$clients] = $this->clients($this->asOf());
        $movedOn = array_filter($clients, fn ($c) => count($c['steps']) > 1);

        $matches = match ($selector['kind']) {
            'path' => array_filter($movedOn, fn ($c) => implode(' > ', $c['steps']) === $selector['key']),
            'same_day' => array_filter($clients, fn ($c) => in_array($selector['key'], $c['steps'], true)),
            'cell' => array_filter($clients, fn ($c) => $this->reachedLater($c, $selector['start'], $selector['reached'])),
            'link' => $this->linkMembers($movedOn, $selector['source'], $selector['target']),
            default => throw new InvalidArgumentException('Unknown selector.'),
        };

        $rows = array_map(fn ($c) => [
            'record' => $c['record'],
            'facility' => $c['facility'],
            'district' => $c['district'],
            'sex' => ['1' => 'Male', '2' => 'Female'][$c['sex']] ?? 'Unknown',
            'age_at_entry' => $c['age'],
            'first_visit' => $c['entry'],
            'pathway' => $this->datedPathway($c['families']),
        ], array_values($matches));
        usort($rows, fn ($a, $b) => [$a['first_visit'], $a['record']] <=> [$b['first_visit'], $b['record']]);

        return ['count' => count($rows), 'rows' => $rows];
    }

    private function setFilters(array $filters): void
    {
        foreach (['from', 'to'] as $k) {
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters[$k] ?? '')) {
                throw new InvalidArgumentException('Invalid period.');
            }
        }
        $this->from = $filters['from'];
        $this->to = $filters['to'];
        $this->district = $filters['district'] ?? null;
        $this->facility = $filters['facility'] ?? null;
        $this->gender = $filters['gender'] ?? null;
        [$this->ageLo, $this->ageHi] = match ($filters['age_band'] ?? '10_19') {
            '10_14' => [10, 14],
            '15_19' => [15, 19],
            'all' => [null, null],
            default => [10, 19],
        };
        $this->window = in_array((int) ($filters['window'] ?? 6), self::WINDOWS, true) ? (int) $filters['window'] : 6;
    }

    /** Same eligibility rule as a where-next cell's numerator in progression(). */
    private function reachedLater(array $c, string $start, string $reached): bool
    {
        $atEntry = array_keys(array_filter($c['families'], fn ($d) => $d === $c['entry']));
        if (! in_array($start, $atEntry, true) || in_array($reached, $atEntry, true)) {
            return false;
        }
        if (in_array($reached, self::FEMALE_ONLY, true) && $c['sex'] !== '2') {
            return false;
        }

        return isset($c['families'][$reached]);
    }

    private function linkMembers(array $movedOn, string $source, string $target): array
    {
        [$ss] = explode('|', $source, 2);
        $labels = $this->flowLabels($movedOn);

        return array_filter($movedOn, function ($c) use ($labels, $source, $target, $ss) {
            $p = $labels[$c['record']];
            $s = (int) $ss;

            return isset($p[$s], $p[$s + 1]) && "{$s}|{$p[$s]}" === $source && ($s + 1)."|{$p[$s + 1]}" === $target;
        });
    }

    /** "ANC (2025-03-01) > PNC (2025-04-04)" - each step with the date it was first reached. */
    private function datedPathway(array $families): string
    {
        $byDate = [];
        foreach ($families as $family => $date) {
            $byDate[$date][] = $family;
        }
        ksort($byDate);

        return implode(' > ', array_map(function ($date) use ($byDate) {
            $fams = $byDate[$date];
            sort($fams);

            return implode(' + ', $fams)." ({$date})";
        }, array_keys($byDate)));
    }

    /** Latest encounter date in the data (entry lag means this trails today). */
    private function asOf(): string
    {
        $row = DB::selectOne('SELECT LEAST(MAX(visit_date), CURDATE()) AS as_of FROM ('.$this->encountersSql().') e');

        return (string) $row->as_of;
    }

    /**
     * @return array{0: list<array{record:string,sex:string,entry:string,families:array<string,string>,steps:list<string>}>, 1:int, 2:int}
     */
    private function clients(string $asOf): array
    {
        [$demog, $bind] = $this->demogSql();
        $enc = $this->encountersSql();
        $family = 'CASE e.instrument '.implode(' ', array_map(
            fn ($inst, $label) => "WHEN '{$inst}' THEN '{$label}'",
            array_keys(self::$FAMILY),
            self::$FAMILY,
        )).' END';

        $rows = DB::select("
            WITH demog AS ({$demog}), enc AS ({$enc}),
            firsts AS (SELECT record, MIN(visit_date) AS first_ever FROM enc WHERE visit_date <= '{$asOf}' GROUP BY record),
            cohort AS (
                SELECT f.record, f.first_ever, d.gender, d.facility, d.district,
                       CASE WHEN d.dob REGEXP '".self::$DATE_RE."' THEN TIMESTAMPDIFF(YEAR, d.dob, f.first_ever) END AS age
                FROM firsts f JOIN demog d ON d.record = f.record
                WHERE {$this->periodCond('f.first_ever')} AND {$this->ageCond('f.first_ever')}
            )
            SELECT c.record, c.first_ever, c.gender, c.facility, c.district, c.age, {$family} AS family, MIN(e.visit_date) AS first_date
            FROM cohort c
            JOIN enc e ON e.record = c.record
            WHERE e.visit_date <= '{$asOf}'
              AND e.visit_date <= DATE_ADD(c.first_ever, INTERVAL {$this->window} MONTH)
            GROUP BY c.record, c.first_ever, c.gender, c.facility, c.district, c.age, family
            ORDER BY c.record, first_date, family
        ", $bind);

        $byRecord = [];
        foreach ($rows as $r) {
            if ($r->family === null) {
                continue;
            }
            $byRecord[$r->record] ??= [
                'record' => $r->record, 'sex' => (string) $r->gender, 'entry' => $r->first_ever, 'families' => [],
                'facility' => $r->facility ?: 'Unknown', 'district' => $r->district ?: 'Unknown',
                'age' => $r->age !== null ? (int) $r->age : null,
            ];
            $byRecord[$r->record]['families'][$r->family] = $r->first_date;
        }

        $cutoff = (new DateTimeImmutable($asOf))->modify("-{$this->window} months")->format('Y-m-d');
        $clients = [];
        $tooRecent = 0;
        foreach ($byRecord as $c) {
            if ($c['entry'] > $cutoff) {
                $tooRecent++;

                continue;
            }
            $c['steps'] = $this->steps($c['families']);
            $clients[] = $c;
        }

        return [$clients, $tooRecent, count($byRecord)];
    }

    /** Same-day first entries collapse into one "A + B" step, in date order. */
    private function steps(array $families): array
    {
        $byDate = [];
        foreach ($families as $family => $date) {
            $byDate[$date][] = $family;
        }
        ksort($byDate);

        return array_values(array_map(function ($fams) {
            sort($fams);

            return implode(' + ', $fams);
        }, $byDate));
    }

    /**
     * "From X, eventually reached Y": rows are entry services (any service in
     * the client's first step), columns every service. A client counts in a
     * cell's denominator unless they already had Y at entry, or Y is
     * girls-only and the client isn't recorded female.
     */
    private function progression(array $clients): array
    {
        $entry = [];
        $movedOn = [];
        $totals = [];
        $denom = [];
        $num = [];
        $days = [];
        $allFamilies = array_values(array_unique(self::$FAMILY));

        foreach ($clients as $c) {
            foreach ($c['families'] as $f => $_) {
                $totals[$f] = ($totals[$f] ?? 0) + 1;
            }
            $atEntry = array_keys(array_filter($c['families'], fn ($d) => $d === $c['entry']));
            $later = array_diff_key($c['families'], array_flip($atEntry));
            foreach ($atEntry as $r) {
                $entry[$r] = ($entry[$r] ?? 0) + 1;
                if ($later !== []) {
                    $movedOn[$r] = ($movedOn[$r] ?? 0) + 1;
                }
            }
            foreach ($allFamilies as $col) {
                if (in_array($col, $atEntry, true) || (in_array($col, self::FEMALE_ONLY, true) && $c['sex'] !== '2')) {
                    continue;
                }
                foreach ($atEntry as $r) {
                    $denom[$r][$col] = ($denom[$r][$col] ?? 0) + 1;
                    if (isset($later[$col])) {
                        $num[$r][$col] = ($num[$r][$col] ?? 0) + 1;
                        $days[$r][$col][] = $this->daysBetween($c['entry'], $later[$col]);
                    }
                }
            }
        }

        arsort($entry);
        arsort($totals);
        $columns = array_keys($totals);

        $rows = [];
        foreach ($entry as $r => $n) {
            $cells = [];
            foreach ($columns as $col) {
                $d = $denom[$r][$col] ?? 0;
                $k = $num[$r][$col] ?? 0;
                $cells[] = [
                    'service' => $col,
                    'self' => $col === $r,
                    'reached' => $k,
                    'eligible' => $d,
                    'suppressed' => $col !== $r && $d < self::MIN_CELL,
                    'pct' => $col !== $r && $d >= self::MIN_CELL ? round($k / $d * 100, 1) : null,
                    'median_days' => $col !== $r && $d >= self::MIN_CELL && $k > 0 ? $this->median($days[$r][$col]) : null,
                ];
            }
            $rows[] = [
                'service' => $r,
                'clients' => $n,
                'moved_on' => $movedOn[$r] ?? 0,
                'moved_on_pct' => round(($movedOn[$r] ?? 0) / $n * 100, 1),
                'cells' => $cells,
            ];
        }

        // Headline findings: the strongest onward links with enough clients
        // behind them to be worth saying out loud.
        $headlines = [];
        foreach ($rows as $row) {
            foreach ($row['cells'] as $cell) {
                if ($cell['pct'] !== null && $cell['eligible'] >= 10 && $cell['reached'] >= self::MIN_CELL) {
                    $headlines[] = ['from' => $row['service'], 'to' => $cell['service']] + $cell;
                }
            }
        }
        usort($headlines, fn ($a, $b) => [$b['pct'], $b['reached']] <=> [$a['pct'], $a['reached']]);

        return ['columns' => $columns, 'rows' => $rows, 'headlines' => array_slice($headlines, 0, 4)];
    }

    /** Stage-by-stage flow of clients who moved on, first FLOW_STEPS steps. */
    private function flow(array $multi): array
    {
        $nodes = [];
        $links = [];
        foreach ($this->flowLabels($multi) as $p) {
            foreach ($p as $stage => $label) {
                $key = $stage.'|'.$label;
                $nodes[$key] = ($nodes[$key] ?? 0) + 1;
                if (isset($p[$stage + 1])) {
                    $lk = $key.'>'.($stage + 1).'|'.$p[$stage + 1];
                    $links[$lk] = ($links[$lk] ?? 0) + 1;
                }
            }
        }

        $outNodes = [];
        foreach ($nodes as $key => $value) {
            [$stage, $label] = explode('|', $key, 2);
            $outNodes[] = ['id' => $key, 'stage' => (int) $stage, 'label' => $label, 'value' => $value];
        }
        $outLinks = [];
        foreach ($links as $key => $value) {
            [$source, $target] = explode('>', $key, 2);
            $outLinks[] = ['source' => $source, 'target' => $target, 'value' => $value];
        }

        return [
            'nodes' => $outNodes,
            'links' => $outLinks,
            'clients' => count($multi),
            'longer_than_shown' => count(array_filter($multi, fn ($c) => count($c['steps']) > self::FLOW_STEPS)),
        ];
    }

    /**
     * Each client's first FLOW_STEPS steps as the flow diagram labels them: a
     * service with fewer than MIN_CELL clients at that step becomes "Other".
     * Shared by flow() and the band drill-down so both group identically.
     *
     * @return array<string, list<string>> record => step labels
     */
    private function flowLabels(array $multi): array
    {
        $paths = [];
        foreach ($multi as $c) {
            $paths[$c['record']] = array_slice($c['steps'], 0, self::FLOW_STEPS);
        }

        $nodeTotals = [];
        foreach ($paths as $p) {
            foreach ($p as $stage => $label) {
                $nodeTotals[$stage][$label] = ($nodeTotals[$stage][$label] ?? 0) + 1;
            }
        }

        return array_map(fn ($p) => array_map(
            fn ($stage, $label) => $nodeTotals[$stage][$label] < self::MIN_CELL ? 'Other' : $label,
            array_keys($p),
            $p,
        ), $paths);
    }

    /**
     * Full pathways of clients who moved on. Every pathway is returned; those
     * with fewer than MIN_CELL clients are flagged `small` - the page hides
     * them behind a toggle (and summarises them as "other"), while the Excel
     * and CSV exports list them all.
     */
    private function paths(array $multi): array
    {
        $groups = [];
        foreach ($multi as $c) {
            $key = implode(' > ', $c['steps']);
            $groups[$key]['steps'] = $c['steps'];
            $groups[$key]['days'][] = $this->daysBetween($c['entry'], max($c['families']));
        }
        uasort($groups, fn ($a, $b) => count($b['days']) <=> count($a['days']));

        $total = count($multi);
        $rows = [];
        $otherPaths = 0;
        $otherClients = 0;
        foreach ($groups as $g) {
            $n = count($g['days']);
            $small = $n < self::MIN_CELL;
            if ($small) {
                $otherPaths++;
                $otherClients += $n;
            }
            $rows[] = [
                'steps' => $g['steps'],
                'clients' => $n,
                'pct' => round($n / $total * 100, 1),
                'median_days' => $this->median($g['days']),
                'small' => $small,
            ];
        }

        return [
            'rows' => $rows,
            'other_paths' => $otherPaths,
            'other_clients' => $otherClients,
            'other_pct' => $total ? round($otherClients / $total * 100, 1) : 0,
        ];
    }

    /**
     * Services combined on one day (any step), e.g. "ANC + HIV testing".
     * Like paths(): every combination is returned, those under MIN_CELL
     * flagged `small` for the page's show/hide toggle; exports list them all.
     */
    private function sameDay(array $clients): array
    {
        $combos = [];
        foreach ($clients as $c) {
            foreach (array_unique($c['steps']) as $step) {
                if (str_contains($step, ' + ')) {
                    $combos[$step] = ($combos[$step] ?? 0) + 1;
                }
            }
        }
        arsort($combos);
        $rows = [];
        $otherCombos = 0;
        $other = 0;
        foreach ($combos as $label => $count) {
            $small = $count < self::MIN_CELL;
            if ($small) {
                $otherCombos++;
                $other += $count;
            }
            $rows[] = ['services' => explode(' + ', $label), 'clients' => $count, 'small' => $small];
        }

        return ['rows' => $rows, 'other_combos' => $otherCombos, 'other_clients' => $other];
    }

    private function daysBetween(string $a, string $b): int
    {
        return (int) (new DateTimeImmutable($a))->diff(new DateTimeImmutable($b))->days;
    }

    private function median(array $values): int
    {
        sort($values);
        $n = count($values);

        return $n % 2 ? $values[intdiv($n, 2)] : (int) round(($values[$n / 2 - 1] + $values[$n / 2]) / 2);
    }
}
