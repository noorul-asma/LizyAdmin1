<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Extends tour_packages with the presentation fields LizyGo's public site
 * needs (subtitle, type, tags, badge, original_price/discount, includes)
 * so the Lizy Admin master dashboard can be a true 1:1 replacement for
 * LizyGo's old local `packages` table, not just a partial one.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('subtitle')->nullable()->after('name');
            $table->string('type')->nullable()->after('subtitle'); // Group Tour / Customised Tour / ...
            $table->json('tags')->nullable()->after('type'); // ["Group Tour","Recommended","Most Booked"]
            $table->json('badge')->nullable()->after('tags'); // {label,color,icon}
            $table->decimal('original_price', 12, 2)->nullable()->after('price');
            $table->string('discount')->nullable()->after('original_price'); // "15% OFF"
            $table->text('includes')->nullable()->after('description');
        });
    }

    public function down(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropColumn(['subtitle', 'type', 'tags', 'badge', 'original_price', 'discount', 'includes']);
        });
    }
};
