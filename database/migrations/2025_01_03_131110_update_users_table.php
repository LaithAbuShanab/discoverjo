<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Rename 'name' column to 'username'
            $table->renameColumn('name', 'username');

            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->date('birthday')->nullable()->after('username');
            $table->string('facebook_id')->nullable()->after('birthday');
            $table->string('google_id')->nullable()->after('facebook_id');
            $table->tinyInteger('sex')->nullable()->comment('1 => male, 2 => female')->index()->after('google_id');
            $table->text('description')->nullable()->after('email');
            $table->string('lang')->default("ar")->after('password');
            $table->string('phone_number')->nullable()->after('lang');
            $table->unsignedMediumInteger('points')->default(0)->after('phone_number');
            $table->decimal('longitude', 9, 6)->nullable()->after('points');
            $table->decimal('latitude', 9, 6)->nullable()->after('longitude');
            $table->tinyInteger('status')->default(2)->comment('0=> inactive, 1=> active, 2=>first login,3=>in active by admin')->index()->after('latitude');
            $table->boolean('is_guide')->default(0)->comment('0=> user, 1=> guide')->index()->after('status');

            $table->fulltext(['first_name', 'last_name', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('username', 'name');

            $table->dropColumn('first_name');
            $table->dropColumn('last_name');
            $table->dropColumn('birthday');
            $table->dropColumn('facebook_id');
            $table->dropColumn('google_id');
            $table->dropColumn('sex');
            $table->dropColumn('description');
            $table->dropColumn('email_verified_at');
            $table->dropColumn('lang');
            $table->dropColumn('phone_number');
            $table->dropColumn('points');
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
            $table->dropColumn('status');
            $table->dropColumn('is_guide');
        });
    }
};
