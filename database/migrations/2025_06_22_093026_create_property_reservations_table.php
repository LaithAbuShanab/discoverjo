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
        Schema::create('property_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('property_period_id')->nullable()->constrained()->nullOnDelete();
            $table->date('check_in');
            $table->date('check_out');
            $table->decimal('total_price', 10, 2)->default(0);
            $table->tinyInteger('status')->comment('0=> pending , 1=> accepted , 2=> rejected,3=>completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('property_reservations');
    }
};
