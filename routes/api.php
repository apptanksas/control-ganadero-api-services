<?php

use App\Http\Controllers\Api\V1\DeleteUserInvitationController;
use App\Http\Controllers\Api\V1\GetUserSubscriptionByFarmController;
use App\Http\Controllers\Api\V1\UpdateAnimalController;
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
});
