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
        Schema::create('service_reservation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_reservation_id')->constrained('service_reservations')->onDelete('cascade');
            $table->tinyInteger('reservation_detail')->comment('1 => adult, 2 => child');
            $table->unsignedInteger('quantity');
            $table->foreignId('price_age_id')->nullable()->constrained('service_price_ages')->nullOnDelete();
            $table->decimal('price_per_unit', 10, 2)->default(0);
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reservation_details');
    }
};
