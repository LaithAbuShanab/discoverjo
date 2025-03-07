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
        Schema::create('question_chains', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('question_id');
            $table->string('type')->nullable();
            $table->string('value')->nullable();
            $table->enum('answer', ['yes', 'no', 'i_dont_know']);
            $table->unsignedBigInteger('next_question_id');
            $table->timestamps();
            $table->foreign('question_id')->references('id')->on('questions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('next_question_id')->references('id')->on('questions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->unique(['question_id', 'answer', 'next_question_id']);

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_chains');
    }
};
