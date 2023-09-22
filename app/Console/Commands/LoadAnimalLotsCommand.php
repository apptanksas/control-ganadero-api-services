<?php

namespace App\Console\Commands;

use App\Models\AnimalLot;
use App\Models\Legacy\Animal;
use App\Models\Lot;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LoadAnimalLotsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load:lots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load the animal lots to to new system lots';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $perPage = 10000;
        $totalAnimals = Animal::query()->whereNotNull([Animal::ATTR_LOTE, Animal::FK_FINCA_ID])
            ->where(Animal::ATTR_LOTE, "!=", "")
            ->where(Animal::ATTR_LOTE, "!=", "NULL")
            ->where(Animal::ATTR_LOTE, "!=", "null")
            ->whereNull(Animal::ATTR_FECHA_BAJA)
            ->whereNotNull(Animal::FK_FINCA_ID)->count();
        $counter = 0;
        $counterSuccess = 0;
        $counterFailure = 0;
        $counterAlreadyExists = 0;
        $currentPage = 1;

        $this->info("TotalAnimals: $totalAnimals");

        while ($counter < $totalAnimals) {

            $this->info("Processing page: $currentPage, counter: $counter");
            /**
             * @var $animals Animal[]
             */
            $animals = Animal::query()->whereNotNull([Animal::ATTR_LOTE, Animal::FK_FINCA_ID])
                ->where(Animal::ATTR_LOTE, "!=", "")
                ->where(Animal::ATTR_LOTE, "!=", "NULL")
                ->where(Animal::ATTR_LOTE, "!=", "null")
                ->whereNull(Animal::ATTR_FECHA_BAJA)
                ->paginate($perPage, [Animal::ATTR_ID, Animal::ATTR_LOTE, Animal::FK_FINCA_ID], "page", $currentPage)->items();

            foreach ($animals as $animal) {

                $name = $animal->getLote();
                $nameNormalized = normalize_text($name);
                $farmId = $animal->getFincaId();
                $animalId = $animal->getId();

                // $this->info("animalId: $animalId, name: $name, farmId: $farmId");

                /**
                 * @var $modelLot Lot
                 */
                $modelLot = Cache::remember("lot_$farmId" . "_$nameNormalized", 3600, function () use ($farmId, $nameNormalized) {
                    return Lot::query()
                        ->where(Lot::ATTR_NAME_NORMALIZED, $nameNormalized)
                        ->where(Lot::FK_FARM_ID, $farmId)->first();
                });

                if ($modelLot != null) {

                    if (AnimalLot::query()->where(AnimalLot::FK_LOT_ID, $modelLot->getId())
                        ->where(AnimalLot::FK_ANIMAL_ID, $animalId)->exists()) {
                        $counterAlreadyExists++;
                    } else {
                        $animalLot = new AnimalLot([
                                AnimalLot::FK_LOT_ID => $modelLot->getId(),
                                AnimalLot::FK_ANIMAL_ID => $animalId
                            ]
                        );
                        $animalLot->saveOrFail();
                        $counterSuccess++;
                    }

                    continue;
                }

                try {


                    DB::transaction(function () use ($name, $nameNormalized, $farmId, $animalId) {

                        $lot = new Lot([
                                Lot::ATTR_NAME => $name,
                                Lot::ATTR_NAME_NORMALIZED => $nameNormalized,
                                Lot::FK_FARM_ID => $farmId
                            ]
                        );

                        $lot->saveOrFail();

                        $animalLot = new AnimalLot([
                                AnimalLot::FK_LOT_ID => $lot->getId(),
                                AnimalLot::FK_ANIMAL_ID => $animalId
                            ]
                        );

                        $animalLot->saveOrFail();

                    }, 3);

                    $counterSuccess++;

                } catch (\Exception $e) {
                    $this->error("[FAILURE SAVE] $farmId => $name ({$e->getMessage()})");
                    $counterFailure++;
                    continue;
                }

            }

            $counter += count($animals);
            $currentPage++;
        }

        $this->info("Load result => Success: $counterSuccess, Failure: $counterFailure, AlreadyExists: $counterAlreadyExists");

        return Command::SUCCESS;
    }
}
