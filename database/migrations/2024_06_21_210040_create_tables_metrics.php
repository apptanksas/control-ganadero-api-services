<?php

use App\Models\Metrics\MetricSubscriptions;
use App\Models\Metrics\MetricUsers;
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
        Schema::create(MetricSubscriptions::TABLE_NAME, function (Blueprint $table) {
            $table->date(MetricSubscriptions::ATTR_DATE)->primary();
            $table->integer(MetricSubscriptions::ATTR_TOTAL_SUBSCRIPTIONS);
            $table->integer(MetricSubscriptions::ATTR_SUBSCRIPTIONS_BY_NEW_USERS);
            $table->integer(MetricSubscriptions::ATTR_EXPIRATIONS);
            $table->timestamps();
        });

        Schema::create(MetricUsers::TABLE_NAME, function (Blueprint $table) {
            $table->date(MetricUsers::ATTR_DATE)->primary();
            $table->integer(MetricUsers::ATTR_TOTAL_USERS);
            $table->integer(MetricUsers::ATTR_NEW_USERS);
            $table->integer(MetricUsers::ATTR_USERS_FREE);
            $table->integer(MetricUsers::ATTR_USERS_PAID);
            $table->integer(MetricUsers::ATTR_TOTAL_COUNT_USERS_ACTIVES);
            $table->integer(MetricUsers::ATTR_COUNT_USERS_ACTIVES_FREE);
            $table->integer(MetricUsers::ATTR_COUNT_USERS_ACTIVES_PAID);
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
        Schema::dropIfExists(MetricSubscriptions::TABLE_NAME);
        Schema::dropIfExists(MetricUsers::TABLE_NAME);
    }
};
