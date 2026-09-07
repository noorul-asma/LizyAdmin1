<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adds LizyGo's "camp pricing" add-on block (per-head/family pricing,
 * jeep safari places, food menu) as free-form JSON, so packages created
 * from LizyGo's own local admin keep this data when synced through the
 * Lizy Admin API instead of losing it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->json('camp_pricing')->nullable()->after('activities');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn('camp_pricing');
        });
    }
};
