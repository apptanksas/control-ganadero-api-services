<?php

use App\Models\Lot;
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
        Schema::create(Lot::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->string(Lot::ATTR_NAME);
            $table->string(Lot::ATTR_NAME_NORMALIZED);
            $table->integer(Lot::FK_FARM_ID);
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
        Schema::dropIfExists(Lot::TABLE_NAME);
    }
};
