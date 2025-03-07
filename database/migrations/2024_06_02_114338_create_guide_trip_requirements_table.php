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
        Schema::create('guide_trip_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guide_trip_id');
            $table->json('item');
            $table->timestamps();
            $table->foreign('guide_trip_id')->references('id')->on('guide_trips')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_trip_requirements');
    }
};
