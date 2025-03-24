<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('delete_counters', function (Blueprint $table) {
            $table->boolean('duration')->default(0)->after('deleted_count');
        });
    }

    public function down(): void
    {
        Schema::table('delete_counters', function (Blueprint $table) {
            $table->dropColumn('duration');
        });
    }
};
