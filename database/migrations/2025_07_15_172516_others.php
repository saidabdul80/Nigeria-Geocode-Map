<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // database/migrations/2024_06_01_create_roles_table.php
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // database/migrations/2024_06_01_create_permissions_table.php
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // database/migrations/2024_06_01_create_role_permission_table.php
        Schema::create('role_permission', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        // database/migrations/2024_06_01_create_user_role_table.php
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
        });

        // database/migrations/2024_06_01_create_user_state_permissions_table.php
        Schema::create('user_state_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

         Schema::create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });
        // database/migrations/2024_06_01_create_user_lga_permissions_table.php
        Schema::create('user_lga_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lga_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
