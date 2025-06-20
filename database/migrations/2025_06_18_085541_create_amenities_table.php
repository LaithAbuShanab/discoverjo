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
        Schema::create('amenities', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->json('name');
            $table->string('slug')->unique();
            $table->integer('priority');
            $table->unsignedBigInteger('parent_id')->nullable(); // FIXED: match type with `id`
            $table->timestamps();
            $table->foreign('parent_id')->references('id')->on('amenities')->onDelete('cascade');
            $table->index('parent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('amenities');
    }
};
