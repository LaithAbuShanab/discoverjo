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
        Schema::create('trash_places', function (Blueprint $table) {
            $table->unsignedMediumInteger('id')->autoIncrement();;
            $table->string('name');
            $table->string('description');
            $table->string('address');
            $table->tinyInteger('business_status')->comment('0->closed,1->operational,2->temporary_closed,3->i do not know')->default(3);
            $table->text('google_map_url');
            $table->decimal('longitude',10,7); //123.4567891
            $table->decimal('latitude',10,7);
            $table->string('phone_number')->nullable();
            $table->tinyInteger('price_level')->comment('-1 do not know 0 Free 1 Inexpensive 2 Moderate 3 Expensive 4 Very Expensive')->default(-1);
            $table->text('website')->nullable();
            $table->decimal('rating', 3, 2)->unsigned()->nullable();
            $table->mediumInteger('total_user_rating')->nullable();
            $table->unsignedTinyInteger('region_id');
            $table->longText('google_place_id')->nullable();
            $table->foreign('region_id')->references('id')->on('regions')->cascadeOnUpdate();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trash_places');
    }
};
