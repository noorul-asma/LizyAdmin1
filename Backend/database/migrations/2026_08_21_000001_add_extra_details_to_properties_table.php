<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // ready_to_move | under_construction
            $table->string('possession_status', 20)->nullable()->after('listing_type');
            // north | south | east | west | north_east | north_west | south_east | south_west
            $table->string('facing', 20)->nullable()->after('plot_area');
            // unfurnished | semi_furnished | fully_furnished
            $table->string('furnishing_status', 20)->nullable()->after('facing');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['possession_status', 'facing', 'furnishing_status']);
        });
    }
};
