<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\BaseModel;
use App\Models\Legacy\Farm;
use App\Models\Legacy\User;
use App\Models\Legacy\UserInvitation;
use App\Models\Legacy\UserSubscription;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Src\Enum\FarmAccess;

class GetUserSubscriptionByFarmController extends ApiController
{

    function __invoke($userId, $farmId)
    {
        try {
            return Cache::remember("get_user_suscription_by_farm_$userId" . "_$farmId", 3600, function () use ($userId, $farmId) {

                $isOwnerFarm = Farm::query()->where(BaseModel::ATTR_ID, $farmId)->where(Farm::FK_USER_ID, $userId)->exists();

                $userIsMember = $this->validateUserMembership($userId);

                $farmAccess = $this->validateFarmAccess($isOwnerFarm, $userId, $farmId, $userIsMember);

                return $this->successResponse([
                    "is_owner" => $isOwnerFarm,
                    "suscription_type" => ($userIsMember) ? "member" : "free",
                    "farm_access" => $farmAccess
                ]);
            });
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse("Internal server error");
        }
    }


    private function validateFarmAccess(bool $isOwnerFarm, $userId, $farmId, $userIsMember)
    {
        $user = User::queryById($userId);

        $invitation = UserInvitation::query()
            ->where(UserInvitation::FK_FARM_ID, $farmId)
            ->where(UserInvitation::ATTR_EMAIL, $user->getEmail())->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })->firstOr("*", function () {
                return null;
            });

        $existsInvitation = !is_null($invitation);
        $userOwnerFarmIsMember = false;

        if ($isOwnerFarm && $userIsMember) {
            return FarmAccess::FULL;
        }

        if ($existsInvitation) {
            $farm = Farm::query()->where(Farm::ATTR_ID, $invitation->getFarmId())->firstOrFail();
            $userOwnerFarmIsMember = is_not_null($invitation) && $this->validateUserMembership($farm->getUserId());
        }

        if ($existsInvitation && $userOwnerFarmIsMember) {
            return FarmAccess::FULL;
        }

        if ($existsInvitation && !$userOwnerFarmIsMember) {
            return FarmAccess::LIMIT;
        }

        if ($isOwnerFarm && !$userIsMember) {
            return FarmAccess::LIMIT;
        }

        return FarmAccess::NONE;
    }

    private function validateUserMembership($userId): bool
    {
        $currentDate = Carbon::now();
        return UserSubscription::query()
            ->where(UserSubscription::FK_USER_ID, $userId)
            ->where(UserSubscription::ATTR_DATE_START, "<=", $currentDate->format("Y-m-d H:i:s"))
            ->where(UserSubscription::ATTR_DATE_END, ">=", $currentDate->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })->exists();
    }
}
