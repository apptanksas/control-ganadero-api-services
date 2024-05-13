<?php

use App\Models\KVS;
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
        Schema::create(KVS::TABLE_NAME, function (Blueprint $table) {
            $table->id();
            $table->integer(KVS::FK_USER_ID);
            $table->string(KVS::ATTR_KEY, 255);
            $table->json(KVS::ATTR_VALUE);
            $table->unique([KVS::FK_USER_ID, KVS::ATTR_KEY]);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(KVS::TABLE_NAME);
    }
};
