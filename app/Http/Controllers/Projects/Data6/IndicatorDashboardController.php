<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\CacheVersion;
use App\Services\Data6\IndicatorService;
use App\Services\Data6\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class IndicatorDashboardController extends Controller
{
    public function show(string $code, IndicatorService $indicators)
    {
        $meta = collect(config('data6_indicators.indicators'))->firstWhere('code', $code);
        abort_if($meta === null, 404);

        return Inertia::render('Data6/IndicatorDeepDive', [
            'appTitle' => config('redcap.data6_unit.title'),
            'meta' => $meta,
            'method' => config('data6_indicators.methods')[$meta['key']] ?? null,
            'methodCommon' => config('data6_indicators.method_common'),
            'filterOptions' => $indicators->filterOptions(),
        ]);
    }

    public function deepDive(string $code, Request $request, ReportService $reports)
    {
        $validated = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'district' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
            'facility' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        $result = Cache::remember(
            CacheVersion::key('deepdive:'.$code.':'.md5(json_encode($validated))),
            now()->addMinutes(15),
            fn () => $reports->deepDive($code, $validated['from'], $validated['to'], $validated['district'] ?? null, $validated['facility'] ?? null),
        );

        abort_if($result === null, 404);

        return response()->json(['period' => $validated, 'indicator' => $result]);
    }

    public function index(IndicatorService $indicators)
    {
        return Inertia::render('Data6/Indicators', [
            'appTitle' => config('redcap.data6_unit.title'),
            'registry' => config('data6_indicators'),
            'filterOptions' => $indicators->filterOptions(),
        ]);
    }

    public function data(Request $request, IndicatorService $indicators)
    {
        $validated = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'district' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
            'facility' => ['nullable', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
            'gender' => ['nullable', 'in:1,2'],
            'age_band' => ['nullable', 'in:10_19,10_14,15_19,all'],
        ]);

        $cacheKey = CacheVersion::key('indicators:'.md5(json_encode($validated)));

        $payload = Cache::remember($cacheKey, now()->addMinutes(15), fn () => $indicators->compute($validated));

        return response()->json(['filters' => $validated] + $payload);
    }
}
