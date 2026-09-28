<?php

namespace App\Services\Data6;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Excel export of a PathwayService::compute() result: Summary / Where next /
 * Pathways / Flow steps / Same day / Method sheets. The Pathways and Same day
 * sheets list every row, small ones included (the page hides those behind a
 * toggle); where-next cells and flow steps under min_cell stay suppressed, as
 * on the page.
 */
class PathwayWorkbook
{
    private const HEADER_FILL = 'FF173B3B';

    private const HEADER_INK = 'FFFFFFFF';

    public function build(array $result, array $filters): Spreadsheet
    {
        $book = new Spreadsheet();
        $book->getProperties()
            ->setTitle("Patient pathways {$filters['from']} to {$filters['to']}")
            ->setCreator('REDCap Stream');

        $this->summarySheet($book->getActiveSheet(), $result, $filters);
        $this->whereNextSheet($book->createSheet(), $result);
        $this->pathsSheet($book->createSheet(), $result);
        $this->flowSheet($book->createSheet(), $result);
        $this->sameDaySheet($book->createSheet(), $result);
        $this->methodSheet($book->createSheet(), $result);

        $book->setActiveSheetIndex(0);

        return $book;
    }

    private function summarySheet(Worksheet $sheet, array $r, array $f): void
    {
        $s = $r['summary'];
        $sheet->setTitle('Summary');
        $sheet->setCellValue('A1', "Patient pathways - first visit {$f['from']} to {$f['to']}");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $age = ['10_14' => '10-14', '15_19' => '15-19', 'all' => 'All ages (QA)'][$f['age_band'] ?? ''] ?? '10-19';
        $sex = ['1' => 'Male', '2' => 'Female'][$f['gender'] ?? ''] ?? 'All';
        $rows = [
            ['Filters', ''],
            ['First visit between', "{$f['from']} to {$f['to']}"],
            ['Follow-up window', "{$r['window_months']} months"],
            ['District', $f['district'] ?? 'All'],
            ['Facility', $f['facility'] ?? 'All'],
            ['Sex', $sex],
            ['Age at entry', $age],
            ['Data up to', $r['as_of']],
            ['', ''],
            ['Measure', 'Value'],
            ['New adolescents (first-ever visit in period)', $s['cohort']],
            ['Entered too recently to follow for the full window', $s['too_recent']],
            ['Followed for the full window', $s['followed']],
            ['Moved on to a new service', $s['moved_on']],
            ['Moved on to a new service (%)', $s['moved_on_pct']],
            ['Several services on the same day only', $s['same_day_only']],
            ['Several services on the same day only (%)', $s['same_day_only_pct']],
            ['Different pathways among those who moved on', $s['distinct_paths']],
        ];
        $sheet->fromArray($rows, null, 'A3');
        $this->styleHeader($sheet, 3, 2);
        $this->styleHeader($sheet, 12, 2);

        $row = 23;
        $sheet->setCellValue("A{$row}", 'Strongest onward links');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        foreach ($r['progression']['headlines'] as $h) {
            $row++;
            $median = $h['median_days'] !== null ? ", typically {$h['median_days']} days after entry" : '';
            $sheet->setCellValue("A{$row}", "Of {$h['eligible']} adolescents who started with {$h['from']}, {$h['pct']}% ({$h['reached']}) later reached {$h['to']}{$median}.");
        }

        $sheet->getColumnDimension('A')->setWidth(52);
        $sheet->getColumnDimension('B')->setWidth(28);
    }

    private function whereNextSheet(Worksheet $sheet, array $r): void
    {
        $sheet->setTitle('Where next');
        $headers = ['Started with', 'Adolescents who started there', 'Moved on (%)', 'Later reached', 'Eligible', 'Reached', 'Reached (%)', 'Median days after entry'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeader($sheet, 1, count($headers));

        $i = 2;
        foreach ($r['progression']['rows'] as $row) {
            foreach ($row['cells'] as $cell) {
                if ($cell['self']) {
                    continue;
                }
                $sheet->fromArray([
                    $row['service'], $row['clients'], $row['moved_on_pct'], $cell['service'],
                    $cell['suppressed'] ? "<{$r['min_cell']}" : $cell['eligible'],
                    $cell['suppressed'] ? 'suppressed' : $cell['reached'],
                    $cell['pct'] ?? '', $cell['median_days'] ?? '',
                ], null, "A{$i}");
                $i++;
            }
        }
        $this->finish($sheet, count($headers));
    }

    private function pathsSheet(Worksheet $sheet, array $r): void
    {
        // Every pathway, including the small ones the page hides by default.
        $sheet->setTitle('Pathways');
        $headers = ['Pathway', 'Steps', 'Adolescents', '% of those who moved on', 'Median days to last step', "Fewer than {$r['min_cell']} adolescents"];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeader($sheet, 1, count($headers));

        $i = 2;
        foreach ($r['paths']['rows'] as $p) {
            $sheet->fromArray([implode(' > ', $p['steps']), count($p['steps']), $p['clients'], $p['pct'], $p['median_days'], $p['small'] ? 'Yes' : ''], null, "A{$i}");
            $i++;
        }
        $this->finish($sheet, count($headers));
    }

    private function flowSheet(Worksheet $sheet, array $r): void
    {
        $sheet->setTitle('Flow steps');
        $headers = ['From step', 'From service', 'To step', 'To service', 'Adolescents'];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeader($sheet, 1, count($headers));

        $rows = array_map(function ($l) {
            [$fs, $fl] = explode('|', $l['source'], 2);
            [$ts, $tl] = explode('|', $l['target'], 2);

            return [(int) $fs + 1, $fl, (int) $ts + 1, $tl, $l['value']];
        }, $r['flow']['links']);
        usort($rows, fn ($a, $b) => [$a[0], $b[4]] <=> [$b[0], $a[4]]);
        if ($rows) {
            $sheet->fromArray($rows, null, 'A2');
        }
        $this->finish($sheet, count($headers));
    }

    private function sameDaySheet(Worksheet $sheet, array $r): void
    {
        // Every combination, including the small ones the page hides by default.
        $sheet->setTitle('Same day');
        $headers = ['Services reached on the same day', 'Adolescents', "Fewer than {$r['min_cell']} adolescents"];
        $sheet->fromArray($headers, null, 'A1');
        $this->styleHeader($sheet, 1, count($headers));

        $i = 2;
        foreach ($r['same_day']['rows'] as $s) {
            $sheet->fromArray([implode(' + ', $s['services']), $s['clients'], $s['small'] ? 'Yes' : ''], null, "A{$i}");
            $i++;
        }
        $this->finish($sheet, count($headers));
    }

    private function methodSheet(Worksheet $sheet, array $r): void
    {
        $sheet->setTitle('Method');
        $notes = [
            'Who is counted: adolescents whose first-ever visit to any service falls in the selected period, aged within the band on that day. Each is followed for '.$r['window_months'].' months from that visit; anyone who entered too recently to have had the full window is left out.',
            'A pathway is the order in which each service was first reached. Repeat visits to the same service do not add steps. Services first reached on the same day share one step, because the data cannot say which came first.',
            'Not included: mental health, health education and counselling have no visit date, so they cannot be placed in a pathway.',
            'Girls-only services: only adolescents recorded as female count towards ANC, PNC and family planning.',
            "Small numbers: where-next cells and flow steps with fewer than {$r['min_cell']} adolescents are suppressed or grouped as Other. The Pathways and Same day sheets list every row; those under {$r['min_cell']} are marked - treat their percentages and timings with caution.",
            'This shows what was recorded, not why. Pathways reflect referral practice and which registers were filled in; registers are transcribed from paper, so recent months may still be filling in.',
        ];
        $sheet->setCellValue('A1', 'How to read this');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        foreach ($notes as $i => $note) {
            $sheet->setCellValue('A'.($i + 3), $note);
        }
        $sheet->getColumnDimension('A')->setWidth(140);
        $sheet->getStyle('A3:A'.(count($notes) + 2))->getAlignment()->setWrapText(true);
    }

    private function finish(Worksheet $sheet, int $cols): void
    {
        for ($c = 1; $c <= $cols; $c++) {
            $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
        }
        $sheet->freezePane('A2');
    }

    private function styleHeader(Worksheet $sheet, int $row, int $cols): void
    {
        $range = 'A'.$row.':'.Coordinate::stringFromColumnIndex($cols).$row;
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setARGB(self::HEADER_INK);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);
        $style->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }
}
