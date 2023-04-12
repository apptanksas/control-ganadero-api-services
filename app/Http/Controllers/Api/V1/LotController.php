<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LotController extends ApiController
{
    function index()
    {

    }

    // v1/lots
    function store(Request $request)
    {
        if (!$request->exists("farm_id") || !$request->exists("name")) {
            return $this->badRequestResponse("Params missing!");
        }

        try {

            $name = $request->get("name");
            $nameNormalized = $this->normalizeText($name);
            $farmId = $request->get("farm_id");

            return Cache::remember("store_lots_$nameNormalized" . "_$farmId", 3600, function () use ($name, $nameNormalized, $farmId) {
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

    function update($id)
    {

    }

    function destroy()
    {

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
}
