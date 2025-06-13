<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add generated columns
        Schema::table('service_categories', function (Blueprint $table) {
            DB::statement("ALTER TABLE service_categories ADD COLUMN name_en VARCHAR(255) AS (JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) STORED");
            DB::statement("ALTER TABLE service_categories ADD COLUMN name_ar VARCHAR(255) AS (JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) STORED");
        });

        // Add fulltext index
        Schema::table('service_categories', function (Blueprint $table) {
            $table->fullText(['name_en', 'name_ar'], 'fulltext_service_name_index');
        });
    }

    public function down(): void
    {
        // Drop fulltext index first
        Schema::table('service_categories', function (Blueprint $table) {
            $table->dropFullText('fulltext_service_name_index');
        });

        // Drop generated columns
        Schema::table('service_categories', function (Blueprint $table) {
            DB::statement("ALTER TABLE service_categories DROP COLUMN name_en");
            DB::statement("ALTER TABLE service_categories DROP COLUMN name_ar");
        });
    }
};
