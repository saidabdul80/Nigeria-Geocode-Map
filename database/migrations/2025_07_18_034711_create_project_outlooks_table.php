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
        // database/migrations/xxxx_create_project_outlooks_table.php
        Schema::create('project_outlooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained()->cascadeOnDelete();
            $table->foreignId('lga_id')->constrained()->cascadeOnDelete();
            $table->integer('outlook');
            $table->year('project_year');
            $table->timestamps();
            
            $table->index(['state_id', 'lga_id', 'project_year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_outlooks');
    }
};
