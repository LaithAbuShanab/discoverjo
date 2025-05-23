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
        Schema::table('events', function (Blueprint $table) {
            DB::statement("ALTER TABLE events ADD COLUMN name_en VARCHAR(255) AS (JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) STORED");
            DB::statement("ALTER TABLE events ADD COLUMN name_ar VARCHAR(255) AS (JSON_UNQUOTE(JSON_EXTRACT(name, '$.ar'))) STORED");
        });
        Schema::table('events', function (Blueprint $table) {
            $table->fullText(['name_en', 'name_ar'], 'fulltext_name_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropFullText('fulltext_name_index');
        });

        Schema::table('events', function (Blueprint $table) {
            DB::statement("ALTER TABLE events DROP COLUMN name_en");
            DB::statement("ALTER TABLE events DROP COLUMN name_ar");
        });
    }
};
