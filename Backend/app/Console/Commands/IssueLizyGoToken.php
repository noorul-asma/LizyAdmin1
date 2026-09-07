<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

/**
 * Provisions (or rotates) the service-account API token LizyGo's own
 * local admin panel uses to write packages into Lizy Admin. This is a
 * separate, non-human "Administrator" account — not your personal login —
 * so LizyGo's server-to-server sync can be revoked independently just by
 * deleting this one user, without touching anyone's real login.
 *
 * Usage: php artisan lizygo:issue-token
 */
class IssueLizyGoToken extends Command
{
    protected $signature = 'lizygo:issue-token {--rotate : Revoke the existing token and issue a brand new one}';

    protected $description = 'Create (or rotate) the API token LizyGo\'s local admin uses to sync with Lizy Admin';

    public function handle(): int
    {
        $administrator = Role::where('slug', 'administrator')->first();

        if (! $administrator) {
            $this->error('No "administrator" role found — run php artisan migrate first.');

            return self::FAILURE;
        }

        $user = User::firstOrCreate(
            ['email' => 'lizygo-sync@lizyadmin.local'],
            [
                'name' => 'LizyGo Sync (service account)',
                'password' => Hash::make(str()->random(40)),
                'role_id' => $administrator->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        if ($this->option('rotate')) {
            $user->tokens()->delete();
        } elseif ($user->tokens()->exists()) {
            $this->warn('This service account already has a token. Run with --rotate to revoke it and issue a new one.');

            return self::SUCCESS;
        }

        $token = $user->createToken('lizygo-sync')->plainTextToken;

        $this->newLine();
        $this->info('Add this to LizyGo\'s .env as LIZY_ADMIN_API_TOKEN (shown once — copy it now):');
        $this->newLine();
        $this->line("  LIZY_ADMIN_API_TOKEN={$token}");
        $this->newLine();

        return self::SUCCESS;
    }
}
