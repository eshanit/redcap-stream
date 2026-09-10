<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\CacheVersion;
use App\Services\Data6\IndicatorService;
use App\Services\Data6\ReportService;
use App\Services\Data6\ReportWorkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(IndicatorService $indicators)
    {
        return Inertia::render('Data6/Reports', [
            'appTitle' => config('redcap.data6_unit.title'),
            'registry' => config('data6_indicators'),
            'filterOptions' => $indicators->filterOptions(),
        ]);
    }

    public function data(Request $request, ReportService $reports)
    {
        $validated = $this->validateFilters($request);

        return response()->json([
            'period' => $validated,
            'report' => $this->cachedReport($reports, $validated),
        ]);
    }

    public function excel(Request $request, ReportService $reports, ReportWorkbook $workbook)
    {
        $validated = $this->validateFilters($request);
        $report = $this->cachedReport($reports, $validated);
        $book = $workbook->build($report, $validated['from'], $validated['to']);
        $scope = implode('', array_map(fn ($v) => "_{$v}", array_filter([$validated['district'] ?? null, $validated['facility'] ?? null])));
        $filename = "AHP_indicators{$scope}_{$validated['from']}_{$validated['to']}.xlsx";

        return new StreamedResponse(function () use ($book) {
            (new Xlsx($book))->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Cache-Control' => 'no-store',
        ]);
    }

    private function validateFilters(Request $request): array
    {
        return $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'district' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
            'facility' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);
    }

    private function cachedReport(ReportService $reports, array $filters): array
    {
        return Cache::remember(
            CacheVersion::key('report:'.md5(json_encode($filters))),
            now()->addMinutes(30),
            fn () => $reports->report($filters['from'], $filters['to'], $filters['district'] ?? null, $filters['facility'] ?? null),
        );
    }
}
