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
        Schema::create('organizerables', function (Blueprint $table) {
            $table->unsignedMediumInteger('id')->autoIncrement();
            $table->unsignedMediumInteger('organizer_id');
            $table->morphs('organizerable');
            $table->timestamps();
            $table->unique(['organizer_id', 'organizerable_id', 'organizerable_type'], 'unique_organizerables');
            $table->foreign('organizer_id')->references('id')->on('organizers')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizerables');
    }
};
