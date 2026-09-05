<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // redcap_data6 is REDCap's long-format table (no primary key). The
        // indicator engine pivots on (project_id, field_name) and joins on
        // (project_id, record); both need composite indexes. Guarded so the
        // migration is safe to re-run and safe on databases where the DBA
        // already added them.
        $this->createIndexIfMissing('redcap_data6', 'idx_data6_pid_field', '(`project_id`, `field_name`, `record`)');
        $this->createIndexIfMissing('redcap_data6', 'idx_data6_pid_record', '(`project_id`, `record`)');
    }

    public function down(): void
    {
        $this->dropIndexIfExists('redcap_data6', 'idx_data6_pid_field');
        $this->dropIndexIfExists('redcap_data6', 'idx_data6_pid_record');
    }

    private function createIndexIfMissing(string $table, string $index, string $columns): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $index]
        );

        if ((int) $exists->c === 0) {
            DB::statement("CREATE INDEX `{$index}` ON `{$table}` {$columns}");
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $exists = DB::selectOne(
            'SELECT COUNT(*) AS c FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$table, $index]
        );

        if ((int) $exists->c > 0) {
            DB::statement("DROP INDEX `{$index}` ON `{$table}`");
        }
    }
};
