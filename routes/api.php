<?php

use App\Http\Controllers\Api\V1\DeleteUserInvitationController;
use App\Http\Controllers\StatisticsController;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::domain(config("app.url"))->prefix("v1")->group(function () {
    Route::delete("user-invitation/{id}", DeleteUserInvitationController::class);

    Route::prefix("statistics")->group(
        function () {
            Route::get("new-users", StatisticsController::class . "@getNewUsers");
            Route::get("subscriptions", StatisticsController::class . "@getNewSubscriptions");
            Route::get("users", StatisticsController::class . "@getUsers");
            Route::get("animals", StatisticsController::class . "@getAnimals");
        }
    );


});
