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
        Schema::create('popular_places', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->unsignedMediumInteger('place_id')->unique();
            $table->decimal('local_price', 5, 2)->nullable();
            $table->decimal('foreign_price', 5, 2)->nullable();
            $table->timestamps();
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('popular_places');
    }
};
