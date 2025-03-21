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
        Schema::table('delete_counters', function (Blueprint $table) {
            $table->string('typeable_type')->nullable()->after('id');
            $table->integer('typeable_id')->nullable()->after('typeable_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('delete_counters', function (Blueprint $table) {
            $table->dropColumn('typeable_type');
            $table->dropColumn('typeable_id');
        });
    }
};
