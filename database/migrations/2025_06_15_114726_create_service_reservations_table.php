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
        Schema::create('service_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('start_time');
            $table->string('contact_info');
            $table->tinyInteger('status')
                ->default(0)
                ->comment('0 => pending, 1 => confirmed, 2 => cancelled');
            $table->decimal('total_price', 10, 2);
            $table->timestamps();
        });
    }



    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_reservations');
    }
};
