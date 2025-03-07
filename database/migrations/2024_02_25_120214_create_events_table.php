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
        Schema::create('events', function (Blueprint $table) {
            $table->unsignedMediumInteger('id')->autoIncrement();
            $table->json('name');
            $table->json('description');
            $table->json('address');
            $table->unsignedTinyInteger('region_id');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->tinyInteger('status')->comment('0->inactive, 1->active')->index();
            $table->decimal('price', 5, 2)->nullable();
            $table->text('link');
            $table->unsignedSmallInteger('attendance_number')->nullable();
            $table->timestamps();
            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
