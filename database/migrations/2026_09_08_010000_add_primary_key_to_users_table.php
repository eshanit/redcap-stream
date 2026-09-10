<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The `users` table was missing AUTO_INCREMENT on `id`, causing every INSERT
 * that relies on MySQL generating it (registration, User::create()) to fail
 * with "Field 'id' doesn't have a default value". This is not consistent
 * across environments: on dev there was no PRIMARY KEY at all, but on the
 * live server a PRIMARY KEY on `id` already existed (confirmed by MySQL
 * rejecting `ADD PRIMARY KEY` with "1068 Multiple primary key defined") -
 * only AUTO_INCREMENT was missing there. Both cases are handled so this
 * migration is safe to run on either.
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->idIsPrimaryKey()) {
            DB::statement('ALTER TABLE users MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT');
        } else {
            DB::statement('ALTER TABLE users MODIFY id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT, ADD PRIMARY KEY (id)');
        }

        if (! $this->hasIndex('users', 'users_email_unique')) {
            Schema::table('users', function ($table) {
                $table->unique('email');
            });
        }
    }

    public function down(): void
    {
        if ($this->hasIndex('users', 'users_email_unique')) {
            Schema::table('users', function ($table) {
                $table->dropUnique('users_email_unique');
            });
        }

        // Deliberately does not DROP PRIMARY KEY - on at least one
        // environment (live) it pre-existed this migration, so removing it
        // here would be destructive on a server this migration didn't
        // create it on. Only the AUTO_INCREMENT this migration added is
        // reverted.
        DB::statement('ALTER TABLE users MODIFY id BIGINT UNSIGNED NOT NULL');
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        return collect(DB::select("SHOW INDEX FROM {$table}"))
            ->contains(fn ($row) => $row->Key_name === $indexName);
    }

    /** Not just "does a PRIMARY KEY exist" - it must actually cover `id`,
     *  since that's the only column AUTO_INCREMENT is being added to. */
    private function idIsPrimaryKey(): bool
    {
        return collect(DB::select("SHOW INDEX FROM users WHERE Key_name = 'PRIMARY'"))
            ->contains(fn ($row) => $row->Column_name === 'id');
    }
};
