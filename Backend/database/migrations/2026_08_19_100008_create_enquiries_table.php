<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enquiries', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('enquiryable'); // enquiryable_type/id - product, property, tour or service
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->text('message')->nullable();
            // Which CTA triggered this: ENQUIRE, REQUEST_QUOTE, CALL, WHATSAPP, SCHEDULE, BOOK ...
            $table->string('cta_type')->nullable();
            $table->enum('status', ['new', 'contacted', 'converted', 'closed'])->default('new');
            $table->string('source')->nullable();
            $table->timestamps();

            // Removed duplicate index because nullableMorphs automatically creates it
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enquiries');
    }
};