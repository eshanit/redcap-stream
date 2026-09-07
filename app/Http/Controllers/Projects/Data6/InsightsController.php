<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\CacheVersion;
use App\Services\Data6\InsightsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class InsightsController extends Controller
{
    public function index()
    {
        return Inertia::render('Data6/Insights', [
            'appTitle' => config('redcap.data6_unit.title'),
        ]);
    }

    public function data(Request $request, InsightsService $insights)
    {
        $validated = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $payload = Cache::remember(
            CacheVersion::key("insights:{$validated['from']}:{$validated['to']}"),
            now()->addMinutes(30),
            fn () => $insights->compute($validated['from'], $validated['to']),
        );

        return response()->json($payload);
    }
}
