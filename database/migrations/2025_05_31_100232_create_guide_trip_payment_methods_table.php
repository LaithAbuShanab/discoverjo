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
        Schema::create('guide_trip_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_trip_id')->constrained()->onDelete('cascade');
            $table->json('method');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guide_trip_payment_methods');
    }
};
