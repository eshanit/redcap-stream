<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\Analysis\ArtCascadeAnalysis;
use App\Services\Data6\Analysis\HtsReconciliationAnalysis;
use App\Services\Data6\CacheVersion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnalysisController extends Controller
{
    public function artCascade(Request $request, ArtCascadeAnalysis $analysis)
    {
        $validated = $request->validate([
            'to' => ['required', 'date_format:Y-m-d'],
        ]);

        $payload = Cache::remember(
            CacheVersion::key("analysis:art_cascade:{$validated['to']}"),
            now()->addMinutes(30),
            fn () => $analysis->compute($validated['to']),
        );

        return response()->json($payload);
    }

    public function htsReconciliation(Request $request, HtsReconciliationAnalysis $analysis)
    {
        $validated = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
        ]);

        $payload = Cache::remember(
            CacheVersion::key("analysis:hts_reconciliation:{$validated['from']}:{$validated['to']}"),
            now()->addMinutes(30),
            fn () => $analysis->compute($validated['from'], $validated['to']),
        );

        return response()->json($payload);
    }
}
