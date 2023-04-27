<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\AnimalLot;
use App\Models\Lot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class GetLotByAnimalIdController extends ApiController
{

    const CACHE_KEY_FORMAT = "get_lot_by_animal_id_%s";
    const CACHE_KEY_BATCH_FORMAT = "animals_in_lot_%s";

    // GET -> lot/animal/{animalId}
    function __invoke($animalId): JsonResponse
    {
        try {
            return Cache::rememberForever(sprintf(self::CACHE_KEY_FORMAT, $animalId), function () use ($animalId) {

                $animalLot = AnimalLot::query()->where(AnimalLot::FK_ANIMAL_ID, $animalId)->firstOrFail(AnimalLot::FK_LOT_ID);

                $lot = Lot::queryById($animalLot->getLotId());

                $cacheKeyAnimalsInLot = sprintf(self::CACHE_KEY_BATCH_FORMAT, $lot->getId());

                /**
                 * @var $animalsInLot array
                 */
                $animalsInLot = Cache::get($cacheKeyAnimalsInLot, []);

                Cache::rememberForever($cacheKeyAnimalsInLot, function () use ($animalsInLot, $animalId) {
                    $animalsInLot[] = sprintf(self::CACHE_KEY_FORMAT, $animalId);
                    return $animalsInLot;
                });

                return $this->successResponse([
                    "id" => $lot->getId(),
                    "name" => $lot->getName(),
                    "animals" => AnimalLot::query()->where(AnimalLot::FK_LOT_ID, $lot->getId())->count()
                ]);
            });
        } catch (\Exception $e) {
            report($e);
            return $this->notFoundResponse();
        }
    }
}
