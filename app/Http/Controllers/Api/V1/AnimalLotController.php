<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\AnimalLot;
use App\Models\Lot;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Src\Util\TTL;

class AnimalLotController extends ApiController
{
    private const CACHE_KEY_INDEX = "index_animal_lots_%s";

    // GET -> v1/lots/{ID}/animals
    function index($lotId, Request $request)
    {
        try {
            return Cache::remember(sprintf(self::CACHE_KEY_INDEX, $lotId), TTL::ONE_MONTH,
                function () use ($lotId) {
                    $result = AnimalLot::query()->where(AnimalLot::FK_LOT_ID, $lotId)->get();
                    $output = [];

                    foreach ($result as $item) {
                        $output[] = [
                            "id" => $item->getId(),
                            "lot_id" => $item->getLotId(),
                            "animal_id" => $item->getAnimalId()
                        ];
                    }

                    return $this->successResponse($output);
                });

        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse();
        }

    }

    // POST -> v1/lots/{ID}/animals
    function store($lotId, Request $request)
    {

        if (!$request->exists("animal_id")) {
            return $this->badRequestResponse("Params missing!");
        }

        try {

            $animalId = $request->get("animal_id");

            return Cache::remember("store_animal_lots_$lotId" . "_$animalId", TTL::ONE_HOUR, function () use ($lotId, $animalId) {

                if (AnimalLot::query()
                    ->where(AnimalLot::FK_LOT_ID, intval($lotId))
                    ->where(AnimalLot::FK_ANIMAL_ID, $animalId)
                    ->exists()
                ) {
                    return $this->badRequestResponse("Animal Lot already exists", "lot_exists");
                }

                $animalLot = new AnimalLot([
                        AnimalLot::FK_LOT_ID => $lotId,
                        AnimalLot::FK_ANIMAL_ID => $animalId
                    ]
                );

                /**
                 * @var $lot Lot
                 */
                $lot = Lot::queryById($lotId);

                $animalLot->saveOrFail();

                $this->removeCacheIndex($lotId);
                $this->removeCacheIndexLots($lot->getFarmId());


                return $this->successResponse(
                    [
                        "id" => $animalLot->getId(),
                        "lot_id" => intval($lotId),
                        "animal_id" => $animalId
                    ]
                );
            });

        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse();
        }
    }

    // DELETE -> v1/lots/{lotIt}/animals/{relationshipId}
    function destroy($lotId, $id)
    {
        try {
            /**
             * @var  $animalLot AnimalLot
             */
            $animalLot = AnimalLot::query()->where(AnimalLot::ATTR_ID, $id)->where(AnimalLot::FK_LOT_ID, $lotId)->firstOrFail();
            $animalLot->deleteOrFail();

            $this->removeCacheIndex($animalLot->getLotId());
            $this->removeCacheStore($lotId, $animalLot->getAnimalId());
            $this->removeCacheLotByAnimaId($animalLot->getAnimalId());

            return $this->successResponse("OK");

        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        } catch (\Throwable $exception) {
            report($exception);
            return $this->internalErrorResponse();
        }
    }

    private function removeCacheIndex($farmId)
    {
        Cache::delete(sprintf(self::CACHE_KEY_INDEX, $farmId));
    }

    private function removeCacheStore($lotId, $animalId)
    {
        Cache::delete("store_animal_lots_$lotId" . "_$animalId");
    }

    private function removeCacheIndexLots($farmId)
    {
        Cache::delete(sprintf("index_lots_%s", $farmId));
    }

    private function removeCacheLotByAnimaId($animalId)
    {
        Cache::delete(sprintf(GetLotByAnimalIdController::CACHE_KEY_FORMAT, $animalId));
    }
}
