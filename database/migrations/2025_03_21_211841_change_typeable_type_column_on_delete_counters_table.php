<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('delete_counters', function (Blueprint $table) {
            $table->string('typeable_type')->change();
        });
    }

    public function down(): void
    {
        Schema::table('delete_counters', function (Blueprint $table) {
            $table->integer('typeable_type')->change();
        });
    }
};
