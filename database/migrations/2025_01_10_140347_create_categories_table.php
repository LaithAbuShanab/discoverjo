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
        Schema::create('categories', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->json('name');
            $table->string('slug')->unique();
            $table->integer('priority');
            $table->unsignedTinyInteger('parent_id')->nullable(); // Parent category ID
            $table->timestamps();

            // Add a foreign key constraint for parent_id
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('cascade');

            // Optional: Add an index for faster lookups on parent_id
            $table->index('parent_id');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
