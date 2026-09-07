<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // Seed the four roles required by the spec.
        DB::table('roles')->insert([
            ['name' => 'Administrator', 'slug' => 'administrator', 'description' => 'Full access to every module.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Can manage, approve and publish assigned content.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Content Editor', 'slug' => 'content-editor', 'description' => 'Can create and edit content, may require approval.', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Viewer', 'slug' => 'viewer', 'description' => 'Read-only access.', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
