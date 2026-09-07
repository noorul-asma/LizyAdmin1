<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->restrictOnDelete(); // property type: Villa, Apartment, Plot ...
            $table->foreignId('location_id')->nullable()->constrained('locations')->nullOnDelete(); // city / area node
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('title');
            $table->string('slug')->unique();
            $table->enum('listing_type', ['sale', 'rent'])->default('sale');
            $table->string('area_name')->nullable(); // free-text micro-area, e.g. "Saravanampatti"
            $table->decimal('price', 14, 2);

            $table->unsignedSmallInteger('bedrooms')->nullable();
            $table->unsignedSmallInteger('bathrooms')->nullable();
            $table->decimal('built_up_area', 10, 2)->nullable(); // sq ft
            $table->decimal('plot_area', 10, 2)->nullable(); // sq ft

            $table->json('amenities')->nullable();
            $table->longText('description')->nullable();

            $table->string('status', 20)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_featured']);
            $table->index(['category_id', 'listing_type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
