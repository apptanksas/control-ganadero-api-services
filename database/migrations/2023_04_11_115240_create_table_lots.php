<?php

use App\Models\AnimalLot;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(AnimalLot::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string(AnimalLot::ATTR_NAME);
            $table->integer(AnimalLot::FK_ANIMAL_ID);
            $table->integer(AnimalLot::FK_FARM_ID);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(AnimalLot::TABLE_NAME);
    }
};
