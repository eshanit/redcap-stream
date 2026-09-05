<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\ReportService;
use App\Services\Data6\ReportWorkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Data6/Reports', [
            'appTitle' => config('redcap.data6_unit.title'),
            'registry' => config('data6_indicators'),
        ]);
    }

    public function data(Request $request, ReportService $reports)
    {
        $validated = $this->validatePeriod($request);

        return response()->json([
            'period' => $validated,
            'report' => $this->cachedReport($reports, $validated['from'], $validated['to']),
        ]);
    }

    public function excel(Request $request, ReportService $reports, ReportWorkbook $workbook)
    {
        $validated = $this->validatePeriod($request);
        $report = $this->cachedReport($reports, $validated['from'], $validated['to']);
        $book = $workbook->build($report, $validated['from'], $validated['to']);
        $filename = "AHP_indicators_{$validated['from']}_{$validated['to']}.xlsx";

        return new StreamedResponse(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store',
        ]);
    }

    private function validatePeriod(Request $request): array
    {
        return $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);
    }

    private function cachedReport(ReportService $reports, string $from, string $to): array
    {
        return Cache::remember(
            "data6:report:{$from}:{$to}",
            now()->addMinutes(30),
            fn () => $reports->report($from, $to),
        );
    }
}
