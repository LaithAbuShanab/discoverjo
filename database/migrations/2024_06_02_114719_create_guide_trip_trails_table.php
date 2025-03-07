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
        Schema::create('guide_trip_trails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guide_trip_id');
            $table->decimal('min_duration_in_minute', 5);
            $table->decimal('max_duration_in_minute', 5);
            $table->decimal('distance_in_meter', 6);
            $table->tinyInteger('difficulty')->comment('0 =>easy 1=>moderate 2=>hard 3=>very hard');
            $table->timestamps();
            $table->foreign('guide_trip_id')->references('id')->on('guide_trips')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_trip_trails');
    }
};
