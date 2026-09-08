<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The live `users` table was missing a primary key / AUTO_INCREMENT on `id`
 * entirely (confirmed via SHOW CREATE TABLE - no PRIMARY KEY, no UNIQUE KEY
 * anywhere on the table), unrelated to and predating the tier migration.
 * Every INSERT that relies on MySQL generating `id` (registration, User::
 * create()) fails with "Field 'id' doesn't have a default value". Existing
 * data was checked first and is clean: 15 rows, all ids distinct and
 * non-null, no duplicate emails - safe to add both constraints directly.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE users MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');

        if (! $this->hasIndex('users', 'users_email_unique')) {
            Schema::table('users', function ($table) {
                $table->unique('email');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function ($table) {
            $table->dropUnique('users_email_unique');
        });

        DB::statement('ALTER TABLE users DROP PRIMARY KEY, MODIFY id BIGINT UNSIGNED NOT NULL');
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM {$table}"))
            ->contains(fn ($row) => $row->Key_name === $indexName);
    }
};
