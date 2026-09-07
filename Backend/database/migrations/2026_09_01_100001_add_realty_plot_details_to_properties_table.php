<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->string('price_unit', 40)->nullable()->after('price');
            $table->decimal('price_max', 14, 2)->nullable()->after('price_unit');
            $table->decimal('plot_area_min', 10, 2)->nullable()->after('plot_area');
            $table->decimal('plot_area_max', 10, 2)->nullable()->after('plot_area_min');
            $table->string('plot_area_unit', 30)->nullable()->after('plot_area_max');
            $table->string('approval_status', 80)->nullable()->after('possession_status');
            $table->boolean('bank_loan_available')->default(false)->after('approval_status');
            $table->decimal('bank_loan_percentage', 5, 2)->nullable()->after('bank_loan_available');
            $table->string('registration_status', 80)->nullable()->after('bank_loan_percentage');
            $table->string('booking_advance', 80)->nullable()->after('registration_status');
            $table->text('nearby_landmarks')->nullable()->after('booking_advance');
            $table->text('special_offer')->nullable()->after('nearby_landmarks');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn([
                'price_unit', 'price_max', 'plot_area_min', 'plot_area_max', 'plot_area_unit',
                'approval_status', 'bank_loan_available', 'bank_loan_percentage',
                'registration_status', 'booking_advance', 'nearby_landmarks', 'special_offer',
            ]);
        });
    }
};
