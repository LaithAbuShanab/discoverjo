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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('username')->unique();
            $table->date('birthday')->nullable();
            $table->string('facebook_id')->nullable();
            $table->string('google_id')->nullable();
            $table->tinyInteger('sex')->nullable()->comment('1 => male, 2 => female')->index();
            $table->string('email')->unique();
            $table->text('description')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('lang')->default("ar");
            $table->string('phone_number')->nullable();
            $table->unsignedMediumInteger('points')->default(0);
            $table->decimal('longitude', 9, 6)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->tinyInteger('status')->default(2)->comment('0=> inactive, 1=> active, 2=>first login,3=>in active by admin')->index();
            $table->boolean('is_guide')->default(0)->comment('0=> user, 1=> guide')->index();
            $table->rememberToken();
            $table->timestamps();

            $table->fulltext(['first_name', 'last_name', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
