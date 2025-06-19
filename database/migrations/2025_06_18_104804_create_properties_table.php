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
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->json('name'); // Multilingual name
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->unsignedTinyInteger('region_id');
            $table->unsignedBigInteger('host_id');
            $table->string('address')->nullable();
            $table->text('google_map_url')->nullable();
            $table->integer('max_guests')->default(0);
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->integer('beds')->default(0);
            $table->integer('status')->default(1);
            $table->timestamps();

            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnUpdate();
            $table->foreign('host_id')->references('id')->on('users')->cascadeOnUpdate();;
        });

        // Add generated columns using raw SQL
        \Illuminate\Support\Facades\DB::statement("
        ALTER TABLE properties
        ADD COLUMN name_en VARCHAR(255)
            AS (JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.en')))
            STORED,
        ADD COLUMN name_ar VARCHAR(255)
            AS (JSON_UNQUOTE(JSON_EXTRACT(`name`, '$.ar')))
            STORED
    ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('properties');
    }
};
