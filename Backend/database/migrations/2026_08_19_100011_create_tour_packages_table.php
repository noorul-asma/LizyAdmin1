<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('destination_name')->nullable(); // free-text fallback, e.g. "Kashmir, India"
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->unsignedSmallInteger('duration_nights')->default(0);
            $table->decimal('price', 12, 2);

            $table->longText('description')->nullable();
            $table->json('itinerary')->nullable();   // [{day:1, title, description}, ...]
            $table->json('inclusions')->nullable();  // ["Breakfast", "Airport pickup", ...]
            $table->json('exclusions')->nullable();
            $table->json('hotels')->nullable();       // [{name, city, rating}, ...]
            $table->json('activities')->nullable();
            $table->longText('terms_conditions')->nullable();

            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};
