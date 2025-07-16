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
       // database/migrations/xxxx_create_user_ward_permissions_table.php
        Schema::create('user_ward_permissions', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ward_id')->constrained()->cascadeOnDelete();
            $table->primary(['user_id', 'ward_id']);
            $table->timestamps();
        });
        Schema::table('records', function (Blueprint $table) {
            $table->integer('ward_id')->nullable()->after('lga_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_ward_permissions');
    }
};
