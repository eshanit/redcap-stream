<?php

namespace App\Http\Controllers\Projects\Data6;

use App\Http\Controllers\Controller;
use App\Services\Data6\SummaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OverviewDashboardController extends Controller
{
    public function __invoke(SummaryService $summary)
    {
        $data = Cache::remember('data6:overview', now()->addMinutes(15), fn () => $summary->compute());

        return Inertia::render('Data6/Overview', [
            'appTitle' => config('redcap.data6_unit.title'),
            'summary' => $data,
        ]);
    }

    /**
     * CSV of unique record IDs for one facility or district
     * (record IDs only - no names or other identifying fields).
     */
    public function exportRecords(Request $request)
    {
        $validated = $request->validate([
            'dimension' => ['required', 'in:facility,district'],
            'value' => ['required', 'string', 'max:30', 'regex:/^[A-Za-z0-9_-]+$/'],
        ]);

        $field = $validated['dimension'] === 'facility' ? 'demog_facility' : 'demog_district';
        $value = $validated['value'];
        $projects = implode(', ', array_map('intval', config('redcap.data6_unit.project_ids')));

        if ($value === 'Unknown') {
            $rows = DB::select("
                SELECT DISTINCT r.record
                FROM redcap_data6 r
                WHERE r.project_id IN ({$projects})
                  AND NOT EXISTS (
                      SELECT 1 FROM redcap_data6 f
                      WHERE f.record = r.record AND f.project_id IN ({$projects})
                        AND f.field_name = ? AND f.value IS NOT NULL AND f.value <> ''
                  )
                ORDER BY CAST(r.record AS UNSIGNED), r.record", [$field]);
        } else {
            $rows = DB::select("
                SELECT DISTINCT record
                FROM redcap_data6
                WHERE project_id IN ({$projects}) AND field_name = ? AND value = ?
                ORDER BY CAST(record AS UNSIGNED), record", [$field, $value]);
        }

        $csv = "record\r\n";
        foreach ($rows as $row) {
            $csv .= '"'.str_replace('"', '""', (string) $row->record)."\"\r\n";
        }

        $filename = "{$validated['dimension']}_{$value}_records_".now()->format('Ymd').'.csv';

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
