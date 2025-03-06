<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plan_activities', function (Blueprint $table) {
            // Add the new foreign key column
            $table->foreignId('plan_day_id')
                ->nullable() // Allows existing records to remain until updated
                ->constrained('plan_days') // References 'id' on the 'plan_days' table
                ->cascadeOnDelete()
                ->after('id');

            // Remove the old day_number column
            $table->dropColumn('day_number');
        });
    }

    public function down(): void
    {
        Schema::table('plan_activities', function (Blueprint $table) {
            // Rollback: Add back the 'day_number' column
            $table->integer('day_number')->nullable();

            // Drop the foreign key and column
            $table->dropForeign(['plan_day_id']);
            $table->dropColumn('plan_day_id');
        });
    }
};
