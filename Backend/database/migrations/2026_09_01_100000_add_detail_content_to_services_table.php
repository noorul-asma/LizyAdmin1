<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds the fields the LizyNet service detail page needs to be fully
 * dynamic (driven from Lizy Admin) instead of static. `features` already
 * existed and is reused as the "what's included" list; this adds the
 * remaining three sections the detail page renders: a longer intro
 * paragraph for the hero, the outcome bullets, and the ordered process
 * steps.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->longText('intro')->nullable()->after('description');
            $table->json('outcomes')->nullable()->after('features');
            $table->json('process')->nullable()->after('outcomes');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['intro', 'outcomes', 'process']);
        });
    }
};
