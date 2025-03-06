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
        Schema::table('plan_activities', function (Blueprint $table) {
                // Drop foreign key constraint first (if it exists)
                $table->dropForeign(['plan_id']);

                // Remove the 'plan_id' column
                $table->dropColumn('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('plan_activities', function (Blueprint $table) {
            $table->foreignId('plan_id')->constrained()->cascadeOnDelete();
        });
    }
};
