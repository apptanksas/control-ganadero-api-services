<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\AnimalLot;
use App\Models\Lot;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Src\Util\TTL;

class LotController extends ApiController
{
    private const CACHE_KEY_INDEX = "index_lots_%s";

    // GET -> v1/lots
    function index(Request $request)
    {
        $farmId = $request->query("farm_id");

        try {

            if (is_null($farmId)) {
                return $this->badRequestResponse("farm_id param missing!");
            }

            return Cache::remember(sprintf(self::CACHE_KEY_INDEX, $farmId), TTL::ONE_MONTH,
                function () use ($farmId) {
                    $result = Lot::query()->where(Lot::FK_FARM_ID, $farmId)->get();
                    $output = [];

                    foreach ($result as $item) {
                        $output[] = [
                            "id" => $item->getId(),
                            "name" => $item->getName(),
                            "animals" => AnimalLot::query()->where(AnimalLot::FK_LOT_ID, $item->getId())->count()
                        ];
                    }

                    return $this->successResponse($output);
                });

        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse();
        }

    }

    // POST -> v1/lots
    function store(Request $request)
    {
        if (!$request->exists("farm_id") || !$request->exists("name")) {
            return $this->badRequestResponse("Params missing!");
        }

        try {

            $name = $request->get("name");
            $nameNormalized = $this->normalizeText($name);
            $farmId = $request->get("farm_id");

            return Cache::remember("store_lots_$nameNormalized" . "_$farmId", TTL::ONE_HOUR, function () use ($name, $nameNormalized, $farmId) {
                if (Lot::query()
                    ->where(Lot::ATTR_NAME_NORMALIZED, $nameNormalized)
                    ->where(Lot::FK_FARM_ID, $farmId)
                    ->exists()
                ) {
                    return $this->badRequestResponse("Lot $name already exists", "lot_exists");
                }


                $lot = new Lot([
                        Lot::ATTR_NAME => $name,
                        Lot::ATTR_NAME_NORMALIZED => $nameNormalized,
                        Lot::FK_FARM_ID => $farmId
                    ]
                );

                $lot->saveOrFail();

                $this->removeCacheIndex($farmId);

                return $this->successResponse(
                    [
                        "id" => $lot->getId(),
                        "name" => $name,
                        "farm_id" => $farmId
                    ]
                );
            });

        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse();
        }
    }

    // PATCH -> v1/lots/{ID}
    function update($id, Request $request)
    {
        if (!$request->exists("name")) {
            return $this->badRequestResponse("Params missing!");
        }

        try {

            $name = $request->get("name");
            $nameNormalized = $this->normalizeText($name);

            return Cache::remember("update_lots_$nameNormalized" . "_$id", TTL::ONE_HOUR, function () use ($id, $name, $nameNormalized) {

                /**
                 * @var  $lot Lot
                 */
                $lot = Lot::queryById($id);

                $lot->updateOrFail([
                    Lot::ATTR_NAME => $name,
                    Lot::ATTR_NAME_NORMALIZED => $nameNormalized
                ]);

                $this->removeCacheIndex($lot->getFarmId());

                return $this->successResponse(
                    [
                        "id" => $lot->getId(),
                        "name" => $name,
                        "farm_id" => $lot->getFarmId()
                    ]
                );
            });

        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        } catch (\Exception $e) {
            report($e);
            return $this->internalErrorResponse();
        }
    }

    // DELETE -> v1/lots/{ID}
    function destroy($id)
    {
        try {
            /**
             * @var  $lot Lot
             */
            $lot = Lot::queryById($id);

            DB::transaction(function () use ($id, $lot) {
                $lot->deleteOrFail();
                AnimalLot::query()->where(AnimalLot::FK_LOT_ID, $id)->delete();
            });

            $normalizedName = $this->normalizeText($lot->getName());

            $this->removeCacheIndex($lot->getFarmId());
            $this->removeCacheStore($normalizedName, $lot->getFarmId());
            $this->removeCacheUpdate($normalizedName, $lot->getId());

            return $this->successResponse("OK");

        } catch (ModelNotFoundException $exception) {
            return $this->notFoundResponse();
        } catch (\Exception $exception) {
            report($exception);
            return $this->internalErrorResponse();
        }
    }

    private function normalizeText($text)
    {
        return trim(
            str_replace(" ", "",
                strtolower(strtr($text, array(
                    'á' => 'a',
                    'é' => 'e',
                    'í' => 'i',
                    'ó' => 'o',
                    'ú' => 'u',
                    'Á' => 'A',
                    'É' => 'E',
                    'Í' => 'I',
                    'Ó' => 'O',
                    'Ú' => 'U',
                    "ñ" => "n",
                    "Ñ" => "N"
                )))));
    }

    private function removeCacheIndex($farmId)
    {
        Cache::delete(sprintf(self::CACHE_KEY_INDEX, $farmId));
    }

    private function removeCacheStore($nameNormalized, $farmId)
    {
        Cache::delete("store_lots_$nameNormalized" . "_$farmId");
    }

    private function removeCacheUpdate($nameNormalized, $id)
    {
        Cache::delete("update_lots_$nameNormalized" . "_$id");
    }
}
