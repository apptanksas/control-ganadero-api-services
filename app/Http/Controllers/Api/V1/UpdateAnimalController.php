<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\Legacy\Animal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class UpdateAnimalController extends ApiController
{
    private $attrsAllowed = [Animal::FK_STATE_REPRODUCTION_ID, Animal::FK_STATE_GROWTH_ID];

    function __invoke($id, $attr, Request $request)
    {
        try {

            if (!in_array($attr, $this->attrsAllowed)) {
                return $this->badRequestResponse("Attr [$attr] not allowed.");
            }

            $value = $request->get("value");

            if (is_null($value)) {
                return $this->badRequestResponse("Value not defined!");
            }

            Schema::disableForeignKeyConstraints();
            $result = Animal::query()->where("id", $id)->update([$attr => $value]) == 1;
            Schema::enableForeignKeyConstraints();

            if ($result) {
                return $this->successResponse("OK");
            } else {
                return $this->internalErrorResponse("Animal couldn't to update, please try again.");
            }

        } catch (\Throwable $e) {
            log_debug($e);
            return $this->internalErrorResponse("Animal couldn't to update, please try again.");
        }
    }
}
