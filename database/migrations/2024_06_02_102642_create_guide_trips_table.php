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
        Schema::create('guide_trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guide_id');
            $table->json('name');
            $table->json('description');
            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');
            $table->decimal('main_price', 5, 2);
            $table->integer('max_attendance');
            $table->tinyInteger('status')->default(1)->comment('0 => inactive , 1=>active');
            $table->timestamps();

            $table->foreign('guide_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_trips');
    }
};
