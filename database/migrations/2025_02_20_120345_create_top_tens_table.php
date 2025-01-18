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
        Schema::create('top_tens', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->unsignedMediumInteger('place_id');
            $table->tinyInteger('rank')->unique();
            $table->timestamps();
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('top_tens');
    }
};
