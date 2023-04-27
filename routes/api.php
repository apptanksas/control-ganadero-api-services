<?php

use App\Http\Controllers\Api\V1\AnimalLotController;
use App\Http\Controllers\Api\V1\DeleteUserInvitationController;
use App\Http\Controllers\Api\V1\GetLotByAnimalIdController;
use App\Http\Controllers\Api\V1\GetUserSubscriptionByFarmController;
use App\Http\Controllers\Api\V1\LotController;
use App\Http\Controllers\Api\V1\UpdateAnimalController;
use App\Http\Controllers\StatisticsController;
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
    Route::patch("animal/{id}/{attr}", UpdateAnimalController::class);
    Route::get("user/{userId}/subscription/{farmId}", GetUserSubscriptionByFarmController::class);

    Route::resource("lots", LotController::class)->only("index", "store", "update", "destroy");
    Route::resource("lots/{id}/animals", AnimalLotController::class)->only("index", "store", "destroy");

    Route::get("lot/animal/{animalId}", GetLotByAnimalIdController::class);

    Route::prefix("statistics")->group(
        function () {
            Route::get("new-users", StatisticsController::class . "@getNewUsers");
            Route::get("subscriptions", StatisticsController::class . "@getNewSubscriptions");
            Route::get("users", StatisticsController::class . "@getUsers");
            Route::get("animals", StatisticsController::class . "@getAnimals");
        }
    );


});
