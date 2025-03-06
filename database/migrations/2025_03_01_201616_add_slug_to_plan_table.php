<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('id'); // Add the column first, allow NULL initially
        });

        // Now update existing records with a unique slug
        DB::statement("UPDATE plans SET slug = CONCAT('plan-', id) WHERE slug IS NULL OR slug = ''");

        // Finally, enforce uniqueness and non-null constraint
        Schema::table('plans', function (Blueprint $table) {
            $table->string('slug')->unique()->change();
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('slug');
        });
    }
};
