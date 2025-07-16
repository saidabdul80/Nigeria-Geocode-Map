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
        Schema::create('records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('lga_id');
            $table->json('data'); // For JSON data storage
            
            // Foreign key constraints (assuming states and lgas tables exist)
            $table->foreign('state_id')
                  ->references('id')
                  ->on('states')
                  ->onDelete('cascade');
                  
            $table->foreign('lga_id')
                  ->references('id')
                  ->on('lgas')
                  ->onDelete('cascade');
                  
            $table->timestamps(); // Adds created_at and updated_at columns
            
            // Indexes for better performance
            $table->index(['state_id', 'lga_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('records');
    }
};