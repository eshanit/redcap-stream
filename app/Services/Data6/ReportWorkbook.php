<?php

namespace App\Services\Data6;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Builds the M&E submission workbook from a ReportService::report() result:
 * Summary / By facility / By district / Definitions sheets.
 */
class ReportWorkbook
{
    private const HEADER_FILL = 'FF173B3B';

    private const HEADER_INK = 'FFFFFFFF';

    public function build(array $report, string $from, string $to): Spreadsheet
    {
        $book = new Spreadsheet();
        $book->getProperties()
            ->setTitle("AHP indicators {$from} to {$to}")
            ->setCreator('REDCap Stream');

        $this->summarySheet($book->getActiveSheet(), $report, $from, $to);
        $this->gridSheet($book->createSheet(), 'By facility', $report, 'facility');
        $this->gridSheet($book->createSheet(), 'By district', $report, 'district');
        $this->definitionsSheet($book->createSheet(), $report);

        $book->setActiveSheetIndex(0);

        return $book;
    }

    private function summarySheet(Worksheet $sheet, array $report, string $from, string $to): void
    {
        $sheet->setTitle('Summary');
        $sheet->setCellValue('A1', "AHP indicator report - {$from} to {$to}");
        $sheet->mergeCells('A1:L1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $headers = ['Code', 'Indicator', 'Level', 'Unit', 'Value', 'Numerator', 'Denominator', '10-14', '15-19', 'Male', 'Female', 'Notes'];
        $sheet->fromArray($headers, null, 'A3');
        $this->styleHeader($sheet, 3, count($headers));

        $r = 4;
        foreach ($report as $ind) {
            $total = $ind['total'];
            $bucket = fn (string $dim, string $label) => $this->bucketValue($ind, $dim, $label);
            $notes = array_filter([
                $ind['status'] === 'proxy' ? 'PROXY' : null,
                $ind['status'] === 'blocked' ? 'NOT COMPUTABLE' : null,
                $ind['no_period'] ? 'All-time value (instrument has no date field)' : null,
                $ind['note'],
            ]);

            $sheet->fromArray([
                $ind['code'],
                $ind['label'],
                $ind['level'],
                $ind['type'] === 'percent' ? 'Percentage' : 'Number',
                $total['value'] ?? null,
                $total['numerator'] ?? null,
                $total['denominator'] ?? null,
                $bucket('age_band', '10-14'),
                $bucket('age_band', '15-19'),
                $bucket('sex', 'Male'),
                $bucket('sex', 'Female'),
                implode(' | ', $notes),
            ], null, "A{$r}");
            $r++;
        }

        foreach (['A' => 10, 'B' => 52, 'C' => 10, 'D' => 12, 'E' => 10, 'F' => 11, 'G' => 12, 'H' => 8, 'I' => 8, 'J' => 8, 'K' => 8, 'L' => 60] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getStyle("B4:B{$r}")->getAlignment()->setWrapText(true);
        $sheet->getStyle("L4:L{$r}")->getAlignment()->setWrapText(true);
        $sheet->freezePane('A4');
    }

    private function gridSheet(Worksheet $sheet, string $title, array $report, string $dim): void
    {
        $sheet->setTitle($title);

        $labels = [];
        foreach ($report as $ind) {
            foreach ($ind['by'][$dim] ?? [] as $bucket) {
                $labels[$bucket['label']] = true;
            }
        }
        $labels = array_keys($labels);
        sort($labels);

        $sheet->fromArray(array_merge(['Code', 'Indicator'], $labels, ['Total']), null, 'A1');
        $this->styleHeader($sheet, 1, count($labels) + 3);

        $r = 2;
        foreach ($report as $ind) {
            $row = [$ind['code'], $ind['label']];
            foreach ($labels as $label) {
                $row[] = $this->bucketValue($ind, $dim, $label);
            }
            $row[] = $ind['total']['value'] ?? null;
            $sheet->fromArray($row, null, "A{$r}");
            $r++;
        }

        $sheet->getColumnDimension('A')->setWidth(10);
        $sheet->getColumnDimension('B')->setWidth(52);
        $sheet->freezePane('C2');
    }

    private function definitionsSheet(Worksheet $sheet, array $report): void
    {
        $registry = collect(config('data6_indicators.indicators'))->keyBy('code');

        $methods = config('data6_indicators.methods', []);

        $sheet->setTitle('Definitions');
        $sheet->setCellValue('A1', 'Common rules: '.config('data6_indicators.method_common', ''));
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getRowDimension(1)->setRowHeight(64);
        $sheet->fromArray(['Code', 'Indicator', 'Definition applied', 'How it was calculated', 'REDCap variables', 'Status', 'Note'], null, 'A2');
        $this->styleHeader($sheet, 2, 7);

        $r = 3;
        foreach ($report as $ind) {
            $meta = $registry->get($ind['code'], []);
            $sheet->fromArray([
                $ind['code'],
                $ind['label'],
                $meta['definition'] ?? '',
                $methods[$ind['key']] ?? '',
                $meta['variables'] ?? '',
                $ind['status'],
                $ind['note'] ?? '',
            ], null, "A{$r}");
            $r++;
        }

        foreach (['A' => 10, 'B' => 40, 'C' => 52, 'D' => 72, 'E' => 36, 'F' => 10, 'G' => 45] as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }
        $sheet->getStyle("A3:G{$r}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);
        $sheet->freezePane('A3');
    }

    private function bucketValue(array $ind, string $dim, string $label): int|float|null
    {
        foreach ($ind['by'][$dim] ?? [] as $bucket) {
            if ($bucket['label'] === $label) {
                return $bucket['value'];
            }
        }

        return null;
    }

    private function styleHeader(Worksheet $sheet, int $row, int $cols): void
    {
        $range = 'A'.$row.':'.\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cols).$row;
        $style = $sheet->getStyle($range);
        $style->getFont()->setBold(true)->getColor()->setARGB(self::HEADER_INK);
        $style->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(self::HEADER_FILL);
        $style->getBorders()->getBottom()->setBorderStyle(Border::BORDER_THIN);
    }
}
