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
        Schema::create('guide_trip_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('guide_trip_id');
            $table->unsignedBigInteger('user_id');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('phone_number', 20); // Set an appropriate length for phone numbers
            $table->unsignedTinyInteger('age'); // Use integer for age
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('guide_trip_id')->references('id')->on('guide_trips')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();

        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_trip_users');
    }
};
