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
        Schema::table('guide_trip_users', function (Blueprint $table) {
            $table->tinyInteger('status')->comment('0: pending, 1: confirmed, 2: canceled')->default(0)->after('age');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('guide_trip_users', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
