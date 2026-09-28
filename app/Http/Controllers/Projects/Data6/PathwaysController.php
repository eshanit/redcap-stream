<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\CacheVersion;
use App\Services\Data6\IndicatorService;
use App\Services\Data6\PathwayService;
use App\Services\Data6\PathwayWorkbook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PathwaysController extends Controller
{
    public function index(IndicatorService $indicators)
    {
        return Inertia::render('Data6/Pathways', [
            'appTitle' => config('redcap.data6_unit.title'),
            'filterOptions' => $indicators->filterOptions(),
            'windows' => PathwayService::WINDOWS,
        ]);
    }

    public function data(Request $request, PathwayService $pathways)
    {
        return response()->json($this->cached($pathways, $this->validateFilters($request)));
    }

    /** The adolescents behind one row/cell/band - see PathwayService::members(). */
    public function members(Request $request, PathwayService $pathways)
    {
        $filters = $this->validateFilters($request);
        $service = ['required', 'string', 'max:60', 'regex:/^[A-Za-z0-9 \/&-]+$/'];
        $selector = $request->validate([
            'kind' => ['required', Rule::in(['path', 'same_day', 'cell', 'link'])],
            'key' => ['required_if:kind,path,same_day', 'string', 'max:400'],
            'start' => array_merge(['required_if:kind,cell'], array_slice($service, 1)),
            'reached' => array_merge(['required_if:kind,cell'], array_slice($service, 1)),
            'source' => ['required_if:kind,link', 'string', 'max:200', 'regex:/^\d\|[A-Za-z0-9 \/&+-]+$/'],
            'target' => ['required_if:kind,link', 'string', 'max:200', 'regex:/^\d\|[A-Za-z0-9 \/&+-]+$/'],
        ]);

        return response()->json(Cache::remember(
            CacheVersion::key('pathways:members:'.md5(json_encode([$filters, $selector]))),
            now()->addMinutes(30),
            fn () => $pathways->members($filters, $selector),
        ));
    }

    public function excel(Request $request, PathwayService $pathways, PathwayWorkbook $workbook)
    {
        $validated = $this->validateFilters($request);
        $book = $workbook->build($this->cached($pathways, $validated), $validated);
        $scope = implode('', array_map(fn ($v) => "_{$v}", array_filter([$validated['district'] ?? null, $validated['facility'] ?? null])));
        $filename = "AHP_pathways_{$validated['window']}m{$scope}_{$validated['from']}_{$validated['to']}.xlsx";

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
            'window' => ['required', 'integer', Rule::in(PathwayService::WINDOWS)],
            'district' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
            'facility' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
            'gender' => ['nullable', Rule::in(['1', '2'])],
            'age_band' => ['nullable', Rule::in(['10_19', '10_14', '15_19', 'all'])],
        ]);
    }

    private function cached(PathwayService $pathways, array $validated): array
    {
        return Cache::remember(
            CacheVersion::key('pathways:'.md5(json_encode($validated))),
            now()->addMinutes(30),
            fn () => $pathways->compute($validated),
        );
    }
}
