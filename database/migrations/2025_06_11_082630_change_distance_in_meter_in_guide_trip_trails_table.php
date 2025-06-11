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
        Schema::table('guide_trip_trails', function (Blueprint $table) {
            // Change precision and make NOT NULL
            $table->decimal('distance_in_meter', 20, 2)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guide_trip_trails', function (Blueprint $table) {
            // Revert to previous type (example: decimal(6), nullable)
            $table->decimal('distance_in_meter', 6)->nullable()->change();
        });
    }

};
