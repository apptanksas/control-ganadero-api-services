<?php


namespace App\Http\Controllers\Api\V1;


use App\Http\Controllers\ApiController;
use App\Models\AnimalLot;
use App\Models\Legacy\Animal;
use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Src\Reports\Legacy\AnimalFormatter;

class ReportLegacyController extends ApiController
{
    // Reporte de animales por lote
    function getReportAnimalsByLot(Request $request)
    {
        $farmId = $request->get("farm_id");
        $lotName = $request->get("lot_name");

        $locale = $request->header("x-locale");
        App::setLocale($locale);

        if (is_null($farmId) || is_null($lotName)) {
            return $this->badRequestResponse("farm_id or lot_name is missing!");
        }

        try {
            $output = Cache::remember("report_animals_by_lot_$farmId" . "_" . $locale . "$lotName", 60 * 5, function () use ($farmId, $lotName) {
                return $this->executeReportAnimalsByLot($farmId, $lotName);
            });

            return $this->successResponse($output);

        } catch (\Throwable $e) {
            report($e);
            return $this->internalErrorResponse();
        }
    }

    // Reporte -> Hembras -> Por lotes
    function getReportFemalesByLot(Request $request)
    {
        $farmId = $request->get("farm_id");
        $lotName = $request->get("lot_name");

        $locale = $request->header("x-locale");
        App::setLocale($locale);

        if (is_null($farmId) || is_null($lotName)) {
            return $this->badRequestResponse("farm_id or lot_name is missing!");
        }

        try {
            $output = Cache::remember("report_female_by_lots_$farmId" . "$lotName", 60 * 5, function () use ($farmId, $lotName) {
                return $this->executeReportFemaleByLot($farmId, $lotName);
            });

            return $this->successResponse($output);

        } catch (\Throwable $e) {
            report($e);
            return $this->internalErrorResponse();
        }

    }

    private function executeReportAnimalsByLot($farmId, $lotName)
    {
        $output = [];

        /**
         * @var $lot Lot
         */
        $lot = Lot::query()->where(Lot::FK_FARM_ID, $farmId)->where(Lot::ATTR_NAME_NORMALIZED, normalize_text($lotName))->first();
        /**
         * @var $animalsLots AnimalLot[]
         */
        $animalsLots = AnimalLot::query()->where(AnimalLot::FK_LOT_ID, $lot->getId())->get([AnimalLot::FK_ANIMAL_ID]);
        $animalsIds = [];

        foreach ($animalsLots as $item) {
            $animalsIds[] = $item->getAnimalId();
        }

        /**
         * @var $animals Animal[]
         */
        $animals = Animal::query()
            ->where(Animal::FK_FINCA_ID, $farmId)
            ->where(function ($builder) {
                return $builder->whereNull(Animal::ATTR_IN_FINCA)->orWhere(Animal::ATTR_IN_FINCA, 1);
            })
            ->where(Animal::ATTR_ESTADO_SALUD_ID, "!=", Animal::ESTADO_SALUD_FALLECIDA)
            ->where(function ($builder) {
                return $builder->whereNull(Animal::ATTR_ESTADO_VENTA_ID)
                    ->orWhere(Animal::ATTR_ESTADO_VENTA_ID, "!=", Animal::ESTADO_VENTA_ANIMAL_VENDIDO);
            })
            ->whereIn(Animal::ATTR_ID, $animalsIds)
            ->get([Animal::ATTR_ID, Animal::ATTR_CODIGO, Animal::ATTR_FECHA_NACIMIENTO, Animal::ATTR_NOMBRE, Animal::ATTR_IS_MACHO]);


        foreach ($animals as $animal) {
            $row = array();
            $row[] = $animal->getCodigo();
            $row[] = AnimalFormatter::getDisplayEdad($animal);
            $row[] = $animal->getNombre();
            $row[] = $animal->isMacho() ? trans("label.animal.attr.male") : trans("label.animal.attr.female");
            $output[] = ["row" => $row, "actions" => array("objectId" => $animal->getId(), "objectType" => "ANIMAL")];
        }

        return $output;
    }

    private function executeReportFemaleByLot($farmId, $lotName)
    {
        $output = [];

        /**
         * @var $lot Lot
         */
        $lot = Lot::query()->where(Lot::FK_FARM_ID, $farmId)->where(Lot::ATTR_NAME_NORMALIZED, normalize_text($lotName))->first();
        /**
         * @var $animalsLots AnimalLot[]
         */
        $animalsLots = AnimalLot::query()->where(AnimalLot::FK_LOT_ID, $lot->getId())->get([AnimalLot::FK_ANIMAL_ID]);
        $animalsIds = [];

        foreach ($animalsLots as $item) {
            $animalsIds[] = $item->getAnimalId();
        }

        /**
         * @var $animals Animal[]
         */
        $animals = Animal::query()
            ->where(Animal::FK_FINCA_ID, $farmId)
            ->where(function ($builder) {
                return $builder->whereNull(Animal::ATTR_IN_FINCA)->orWhere(Animal::ATTR_IN_FINCA, 1);
            })
            ->where(Animal::ATTR_ESTADO_SALUD_ID, "!=", Animal::ESTADO_SALUD_FALLECIDA)
            ->where(function ($builder) {
                return $builder->whereNull(Animal::ATTR_ESTADO_VENTA_ID)
                    ->orWhere(Animal::ATTR_ESTADO_VENTA_ID, "!=", Animal::ESTADO_VENTA_ANIMAL_VENDIDO);
            })
            ->where(function ($builder) {
                return $builder->whereNull(Animal::ATTR_IS_MACHO)
                    ->orWhere(Animal::ATTR_IS_MACHO, "!=", 1);
            })
            ->whereIn(Animal::ATTR_ID, $animalsIds)
            ->get([Animal::ATTR_ID]);


        foreach ($animals as $animal) {
            $output[] = $animal->getId();
        }

        return $output;
    }
}
