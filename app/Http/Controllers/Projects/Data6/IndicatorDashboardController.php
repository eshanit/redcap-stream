<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\IndicatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class IndicatorDashboardController extends Controller
{
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

        $cacheKey = 'data6:indicators:'.md5(json_encode($validated));

        $payload = Cache::remember($cacheKey, now()->addMinutes(15), fn () => $indicators->compute($validated));

        return response()->json(['filters' => $validated] + $payload);
    }
}
