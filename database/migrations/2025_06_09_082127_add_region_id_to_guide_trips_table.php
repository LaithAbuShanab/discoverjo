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
        Schema::table('guide_trips', function (Blueprint $table) {
            $table->unsignedTinyInteger('region_id')->after('guide_id');
            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnUpdate();
        });
    }

    public function down(): void
    {
        Schema::table('guide_trips', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });
    }

};
