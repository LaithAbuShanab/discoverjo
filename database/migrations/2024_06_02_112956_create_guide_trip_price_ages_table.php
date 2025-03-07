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
        Schema::create('guide_trip_price_ages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guide_trip_id');
            $table->integer('min_age');
            $table->integer('max_age');
            $table->decimal('price', 5, 2);
            $table->timestamps();
            $table->foreign('guide_trip_id')->references('id')->on('guide_trips')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_trip_price_ages');
    }
};
