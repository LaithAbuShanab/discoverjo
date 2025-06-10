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
        Schema::create('service_service_categories', function (Blueprint $table) {
            $table->id();

            // These must match the referenced tables' ID types
            $table->unsignedBigInteger('service_id');
            $table->unsignedTinyInteger('service_category_id');

            $table->timestamps();

            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('service_category_id')->references('id')->on('service_categories')->cascadeOnDelete()->cascadeOnUpdate();

            $table->unique(['service_id', 'service_category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_service_categories');
    }
};
