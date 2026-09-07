<?php

namespace App\Services\Data6;

/**
 * Self-invalidating cache-key prefix for the data6 dashboards.
 *
 * Every cached response derived from the indicator/report/insights/summary
 * engines is keyed with this version. It is a hash of those files' last-
 * modified times, so editing any of them (a deploy, a definition change)
 * changes the version and every dependent cache key automatically misses
 * on the next request - no manual cache:clear needed after a code change.
 * A stale entry left behind by the old version simply expires on its own
 * TTL; it is never read again because its key no longer matches.
 */
class CacheVersion
{
    private static ?string $version = null;

    public static function current(): string
    {
        if (self::$version !== null) {
            return self::$version;
        }

        $files = [
            app_path('Services/Data6/QueryFragments.php'),
            app_path('Services/Data6/IndicatorService.php'),
            app_path('Services/Data6/ReportService.php'),
            app_path('Services/Data6/ReportWorkbook.php'),
            app_path('Services/Data6/InsightsService.php'),
            app_path('Services/Data6/SummaryService.php'),
            app_path('Services/Data6/Analysis/ArtCascadeAnalysis.php'),
            app_path('Services/Data6/Analysis/HtsReconciliationAnalysis.php'),
            config_path('data6_indicators.php'),
        ];

        $stamp = '';
        foreach ($files as $file) {
            $stamp .= '|'.(is_file($file) ? filemtime($file) : 0);
        }

        return self::$version = substr(md5($stamp), 0, 10);
    }

    public static function key(string $suffix): string
    {
        return 'data6:'.self::current().':'.$suffix;
    }
}
