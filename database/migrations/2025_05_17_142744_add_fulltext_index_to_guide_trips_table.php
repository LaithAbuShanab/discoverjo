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
        // إنشاء الأعمدة المشتقة من JSON
        DB::statement("
            ALTER TABLE guide_trips
            ADD COLUMN name_en TEXT GENERATED ALWAYS AS (json_unquote(json_extract(name, '$.en'))) STORED,
            ADD COLUMN name_ar TEXT GENERATED ALWAYS AS (json_unquote(json_extract(name, '$.ar'))) STORED,
            ADD COLUMN description_en TEXT GENERATED ALWAYS AS (json_unquote(json_extract(description, '$.en'))) STORED,
            ADD COLUMN description_ar TEXT GENERATED ALWAYS AS (json_unquote(json_extract(description, '$.ar'))) STORED
        ");

        // إضافة فهرس FULLTEXT عبر Blueprint
        Schema::table('guide_trips', function (Blueprint $table) {
            $table->fullText(['name_en', 'name_ar'], 'guide_trips_fulltext_name');
            $table->fullText(['description_en', 'description_ar'], 'guide_trips_fulltext_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // حذف الفهارس أولًا
        Schema::table('guide_trips', function (Blueprint $table) {
            $table->dropFullText('guide_trips_fulltext_name');
            $table->dropFullText('guide_trips_fulltext_description');
        });

        // ثم حذف الأعمدة المشتقة
        DB::statement("
            ALTER TABLE guide_trips
            DROP COLUMN name_en,
            DROP COLUMN name_ar,
            DROP COLUMN description_en,
            DROP COLUMN description_ar
        ");
    }
};
