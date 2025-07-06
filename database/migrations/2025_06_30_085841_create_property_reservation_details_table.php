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
        Schema::create('property_reservation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_reservation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_period_id')->constrained()->cascadeOnDelete();
            $table->dateTime('from_datetime');
            $table->dateTime('to_datetime');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_reservation_details');
    }
};
