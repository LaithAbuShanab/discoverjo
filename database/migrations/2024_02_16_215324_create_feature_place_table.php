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
        Schema::create('feature_place', function (Blueprint $table) {
            $table->id();
            $table->unsignedMediumInteger('place_id');
            $table->unsignedSmallInteger('feature_id');
            $table->timestamps();
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('feature_id')->references('id')->on('features')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['place_id', 'feature_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_place');
    }
};
