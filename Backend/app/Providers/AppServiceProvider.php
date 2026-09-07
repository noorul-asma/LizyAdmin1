<?php

namespace App\Providers;

use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // MySQL/MariaDB compatibility fix: every migration in this project
        // creates unique string columns (users.email, *.slug, settings.key,
        // etc.) with $table->string('col')->unique() and no explicit
        // length, which defaults to VARCHAR(255). Combined with the
        // utf8mb4 charset (4 bytes per character - config/database.php),
        // that produces a 255*4 = 1020-byte index, which exceeds this
        // server's key-length limit ("Specified key was too long; max key
        // length is 1000 bytes" - MyISAM's fixed limit, hit here because
        // this MySQL install's default table engine isn't InnoDB; the same
        // fix is also safe under InnoDB's stricter 767-byte legacy limit).
        // Capping the default string length at 191 characters keeps every
        // such index at 191*4 = 764 bytes, comfortably under all of these
        // limits, without editing a single migration file. This is
        // Laravel's own documented fix for exactly this situation and only
        // affects the DEFAULT length used when a migration doesn't specify
        // one - migrations that already pass an explicit length (e.g.
        // personal_access_tokens.token, string('token', 64)) are
        // unaffected.
        SchemaBuilder::defaultStringLength(191);
    }
}
