<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\BaseModel;
use App\Models\Legacy\Farm;
use App\Models\Legacy\UserSubscription;

class GetUserSubscriptionByFarmController extends ApiController
{

    function __invoke($userId, $farmId)
    {
        $isOwnerFarm = Farm::query()->where(BaseModel::ATTR_ID, $farmId)->where(Farm::FK_USER_ID, $userId)->exists();
        $userIsMember = UserSubscription::query()->where(UserSubscription::FK_USER_ID,$userId);

        return $this->successResponse([
            "is_owner" => $isOwnerFarm,
            "suscription_type" => "free",
            "farm_access" => "free"
        ]);
    }
}
