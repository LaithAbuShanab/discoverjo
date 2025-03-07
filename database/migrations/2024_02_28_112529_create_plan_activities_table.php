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
        Schema::create('plan_activities', function (Blueprint $table) {
            $table->unsignedMediumInteger('id')->autoIncrement();
            $table->unsignedMediumInteger('plan_id');
            $table->tinyInteger('day_number');
            $table->unsignedMediumInteger('place_id');
            $table->json('activity_name');
            $table->time('start_time');
            $table->time('end_time');
            $table->json('notes')->nullable();
            $table->timestamps();
            $table->foreign('plan_id')->references('id')->on('plans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_activities');
    }
};
