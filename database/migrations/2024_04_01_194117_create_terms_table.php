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
        Schema::create('terms', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->autoIncrement();
            $table->unsignedTinyInteger('legal_document_id');
            $table->json('content');
            $table->timestamps();
            $table->foreign('legal_document_id')->references('id')->on('legal_documents')->cascadeOnDelete()->cascadeOnUpdate();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terms');
    }
};
