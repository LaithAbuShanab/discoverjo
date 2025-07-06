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
        Schema::table('property_reservations', function (Blueprint $table) {
            // Drop property_period_id
            $table->dropForeign(['property_period_id']);
            $table->dropColumn('property_period_id');

            // Change check_in and check_out to datetime
            $table->dateTime('check_in')->change();
            $table->dateTime('check_out')->change();
        });
    }

    public function down(): void
    {
        Schema::table('property_reservations', function (Blueprint $table) {
            // Revert to original
            $table->foreignId('property_period_id')->nullable()->constrained()->nullOnDelete();
            $table->date('check_in')->change();
            $table->date('check_out')->change();
        });
    }
};
