<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\Legacy\UserInvitation;
use Illuminate\Http\JsonResponse;

class DeleteUserInvitationController extends ApiController
{
    function __invoke($id): JsonResponse
    {
        try {
            /**
             * @var $userInvitation UserInvitation
             */
            $userInvitation = UserInvitation::queryById($id);
            if ($userInvitation->delete()) {
                return $this->successResponse("OK");
            } else {
                return $this->internalErrorResponse("User invitation didn't could deleted, try again");
            }
        } catch (\Throwable $e) {
            return $this->badRequestResponse("User invitation not found.");
        }
    }
}
