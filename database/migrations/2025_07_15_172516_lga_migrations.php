<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('lgas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('state_id');
            $table->string('name');
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->timestamps();
        });
        
    }

    public function down()
    {
        Schema::dropIfExists('lgas');
    }
};
