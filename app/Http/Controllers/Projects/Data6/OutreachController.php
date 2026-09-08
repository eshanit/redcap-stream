<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\Analysis\OutreachWorklistService;
use App\Services\Data6\CacheVersion;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;

class OutreachController extends Controller
{
    public function index()
    {
        return Inertia::render('Data6/Outreach', [
            'appTitle' => config('redcap.data6_unit.title'),
        ]);
    }

    public function data(OutreachWorklistService $service)
    {
        $asOf = now()->format('Y-m-d');

        $payload = Cache::remember(
            CacheVersion::key("outreach:{$asOf}"),
            now()->addMinutes(30),
            fn () => $service->compute($asOf),
        );

        return response()->json($payload);
    }
}
