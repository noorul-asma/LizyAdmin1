<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time data migration: copies every row from the OLD SQLite database
 * (database/database.sqlite) into the NEW MySQL database configured in
 * .env (DB_CONNECTION=mysql), table by table, preserving primary keys so
 * every foreign key relationship (media -> service, product -> category,
 * etc.) still points at the right row after the move.
 *
 * This does NOT touch or delete the SQLite file - it only reads from it.
 * Safe to re-run: each table is truncated on the MySQL side immediately
 * before its own data is re-copied, so running it twice just re-syncs
 * rather than duplicating rows.
 *
 * USAGE:
 *   1. Make sure .env has DB_CONNECTION=mysql and the migrations have
 *      already been run against MySQL (php artisan migrate --force).
 *   2. Make sure database/database.sqlite (the OLD data) still exists in
 *      this project folder - this command reads it directly regardless
 *      of what DB_CONNECTION is set to.
 *   3. Run: php artisan app:migrate-sqlite-to-mysql
 */
class MigrateSqliteDataToMysql extends Command
{
    protected $signature = 'app:migrate-sqlite-to-mysql
        {--sqlite-path= : Path to the old database.sqlite file (defaults to database/database.sqlite)}';

    protected $description = 'Copy all existing data from the old SQLite database into the configured MySQL database';

    /**
     * Order matters: a table with a foreign key must be copied AFTER the
     * table it points to. media is copied last of the "content" tables
     * because it can reference products/properties/tour_packages/services.
     */
    private const TABLE_ORDER = [
        'roles',
        'permissions',
        'permission_role',
        'users',
        'personal_access_tokens',
        'locations',
        'categories',
        'settings',
        'products',
        'properties',
        'tour_packages',
        'services',
        'media',
        'enquiries',
    ];

    public function handle(): int
    {
        if (config('database.default') !== 'mysql') {
            $this->error('DB_CONNECTION in .env is not "mysql". Set it to mysql, run "php artisan migrate --force" first, then re-run this command.');

            return self::FAILURE;
        }

        $sqlitePath = $this->option('sqlite-path') ?: database_path('database.sqlite');

        if (! file_exists($sqlitePath)) {
            $this->error("Could not find the old SQLite database at: {$sqlitePath}");
            $this->line('Pass --sqlite-path=/full/path/to/database.sqlite if it lives somewhere else.');

            return self::FAILURE;
        }

        // Register a temporary second connection pointing at the old file,
        // completely separate from the app's real ("mysql") connection.
        config(['database.connections.sqlite_source' => [
            'driver' => 'sqlite',
            'database' => $sqlitePath,
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        $source = DB::connection('sqlite_source');
        $target = DB::connection(); // the app's default (mysql) connection

        $this->info("Source (old data):  SQLite at {$sqlitePath}");
        $this->info('Target (new data):  MySQL database "'.config('database.connections.mysql.database').'"');
        $this->newLine();

        $target->statement('SET FOREIGN_KEY_CHECKS=0');

        $summary = [];

        foreach (self::TABLE_ORDER as $table) {
            if (! Schema::connection('sqlite_source')->hasTable($table) || ! Schema::hasTable($table)) {
                continue;
            }

            $rows = $source->table($table)->get();

            if ($rows->isEmpty()) {
                $summary[$table] = 0;

                continue;
            }

            $target->table($table)->truncate();

            // Insert in chunks of 200 so very large tables (media, in
            // particular) never hit MySQL's max_allowed_packet in one go.
            $rows->chunk(200)->each(function ($chunk) use ($target, $table) {
                $target->table($table)->insert(
                    $chunk->map(fn ($row) => (array) $row)->all()
                );
            });

            $summary[$table] = $rows->count();
            $this->info("Copied {$table}: {$rows->count()} row(s).");
        }

        $target->statement('SET FOREIGN_KEY_CHECKS=1');

        $this->newLine();
        $this->info('Done. Row counts copied into MySQL:');
        $this->table(['Table', 'Rows'], collect($summary)->map(fn ($n, $t) => [$t, $n])->values());

        $this->newLine();
        $this->comment('Nothing was deleted from the old SQLite file - it is untouched and can be kept as a backup.');

        return self::SUCCESS;
    }
}
