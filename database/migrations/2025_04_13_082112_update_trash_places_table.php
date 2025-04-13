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
        Schema::table('trash_places', function (Blueprint $table) {

            // Ensure 'google_place_id' exists
            if (!Schema::hasColumn('trash_places', 'google_place_id')) {
                $table->longText('google_place_id')->nullable();
            }
            // Drop all columns except 'id', 'google_place_id', and timestamps
            $table->dropColumn([
                'name',
                'description',
                'address',
                'business_status',
                'google_map_url',
                'longitude',
                'latitude',
                'phone_number',
                'price_level',
                'website',
                'rating',
                'total_user_rating',
                'region_id'
            ]);

            // Remove foreign key

        });
    }

    public function down(): void
    {
        Schema::table('trash_places', function (Blueprint $table) {
            $table->string('name');
            $table->string('description');
            $table->string('address');
            $table->tinyInteger('business_status')->default(3)->comment('0->closed,1->operational,2->temporary_closed,3->i do not know');
            $table->text('google_map_url');
            $table->decimal('longitude',10,7);
            $table->decimal('latitude',10,7);
            $table->string('phone_number')->nullable();
            $table->tinyInteger('price_level')->default(-1)->comment('-1 do not know 0 Free 1 Inexpensive 2 Moderate 3 Expensive 4 Very Expensive');
            $table->text('website')->nullable();
            $table->decimal('rating', 3, 2)->unsigned()->nullable();
            $table->mediumInteger('total_user_rating')->nullable();
            $table->unsignedTinyInteger('region_id');
            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnUpdate();
        });
    }
};
