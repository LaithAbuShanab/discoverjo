<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use  Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {

        Schema::table('places', function (Blueprint $table) {
            DB::statement("ALTER TABLE places ADD COLUMN name_en VARCHAR(255) AS (JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) STORED");
            DB::statement("ALTER TABLE places ADD COLUMN name_ar VARCHAR(255) AS (JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) STORED");
        });

        // Add fulltext index using Laravel's FullTextIndex
        Schema::table('places', function (Blueprint $table) {
            $table->fullText(['name_en', 'name_ar'], 'fulltext_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop fulltext index and columns
        Schema::table('places', function (Blueprint $table) {
            $table->dropFullText('fulltext_name_index');
        });

        Schema::table('places', function (Blueprint $table) {
            DB::statement("ALTER TABLE places DROP COLUMN name_en");
            DB::statement("ALTER TABLE places DROP COLUMN name_ar");
        });
    }
};
