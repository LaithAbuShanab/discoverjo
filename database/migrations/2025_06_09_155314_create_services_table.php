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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->morphs('provider');
            $table->json('name');
            $table->string('slug')->nullable()->unique();
            $table->json('description');
            $table->decimal('price', 5, 2);
            $table->unsignedTinyInteger('region_id');
            $table->json('address')->nullable();
            $table->text('url_google_map')->nullable();
            $table->tinyInteger('status')->comment('0->inactive, 1->active')->index();
            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
