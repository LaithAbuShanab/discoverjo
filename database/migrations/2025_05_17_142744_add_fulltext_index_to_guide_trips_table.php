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
        DB::statement("
            ALTER TABLE guide_trips
            ADD COLUMN name_en TEXT GENERATED ALWAYS AS (json_unquote(json_extract(name, '$.en'))) STORED AFTER status,
            ADD COLUMN name_ar TEXT GENERATED ALWAYS AS (json_unquote(json_extract(name, '$.ar'))) STORED AFTER name_en
        ");

        Schema::table('guide_trips', function (Blueprint $table) {
            $table->fullText(['name_en', 'name_ar'], 'guide_trips_fulltext_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guide_trips', function (Blueprint $table) {
            $table->dropFullText('guide_trips_fulltext_name');
        });

        DB::statement("
            ALTER TABLE guide_trips
            DROP COLUMN name_en,
            DROP COLUMN name_ar
        ");
    }
};
