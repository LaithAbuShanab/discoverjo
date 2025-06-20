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
        Schema::create('property_availability_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_availability_id')->constrained('property_availabilities')->onDelete('cascade');
            $table->foreignId('property_period_id')->constrained('property_periods')->onDelete('cascade');
            $table->enum('day_of_week', ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);
            $table->decimal('price',10,2);
            $table->timestamps();
            $table->unique(['property_availability_id', 'property_period_id','day_of_week'], 'unique_availability_day');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_availability_days');
    }
};
