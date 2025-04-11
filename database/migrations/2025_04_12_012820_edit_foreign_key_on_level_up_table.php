<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table(config('level-up.table'), function (Blueprint $table) {
            // First, drop the existing foreign key
            $table->dropForeign([config('level-up.user.foreign_key')]);

            // Then, add the new one with ON DELETE CASCADE
            $table->foreign(config('level-up.user.foreign_key'))
                ->references('id')
                ->on(config('level-up.user.users_table'))
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::table(config('level-up.table'), function (Blueprint $table) {
            // Drop the updated foreign key
            $table->dropForeign([config('level-up.user.foreign_key')]);

            // Re-add the original foreign key without cascading delete
            $table->foreign(config('level-up.user.foreign_key'))
                ->references('id')
                ->on(config('level-up.user.users_table'));
        });
    }
};
