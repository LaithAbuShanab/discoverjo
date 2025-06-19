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
        Schema::create('property_periods', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('type')->comment('1=>morning, 2=>evening, 3=>overnight');
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_periods');
    }
};
