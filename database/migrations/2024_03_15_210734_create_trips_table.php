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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedMediumInteger('place_id');
            $table->tinyInteger('trip_type')->comment('0: public, 1: followers, 2: specific_users');
            $table->string('name');
            $table->text('description');
            $table->decimal('cost', 5, 2);
            $table->json('age_range')->nullable();
            $table->tinyInteger('sex')->comment('0: Both, 1: Male, 2: Female');
            $table->dateTime('date_time');
            $table->integer('attendance_number')->nullable();
            $table->tinyInteger('status')->comment('0: inactive, 1: active, 2: deleted_by_creator, 3: deleted_by_admin')->default(1);
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('place_id')->references('id')->on('places')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['user_id', 'place_id', 'date_time', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
