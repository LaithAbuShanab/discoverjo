<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_guide');
            $table->tinyInteger('type')->default(1)->after('status')->comment('1 => user, 2 => guide, 3=>provider, 4=>chalet provider')->index();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->boolean('is_guide')->default(1)->comment('1 => user, 2 => guide, 3=>provider, 4=>chalet provider')->index();
        });
    }
};
