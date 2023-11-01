<?php

namespace App\Console\Commands;

use App\Models\AnimalLot;
use App\Models\Legacy\Animal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RemoveDuplicateAnimalLotsCommand extends Command
{
    const COMMAND = 'maintenance:remove-duplicate-animal-lots';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove duplicate animal lots';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * SELECT animal_id, COUNT(*) as count FROM `nv_animal_lots` GROUP BY animal_id HAVING count > 1
         */
        $result = AnimalLot::query()->select(AnimalLot::FK_ANIMAL_ID, DB::raw("COUNT(*) as count"))
            ->groupBy(AnimalLot::FK_ANIMAL_ID)
            ->havingRaw("count > 1")
            ->get();

        $animalsIds = [];

        foreach ($result as $item) {
            $animalsIds[] = $item->animal_id;
        }

        /**
         * Get all animal lots with duplicate animal_id
         */
        $animalsLots = AnimalLot::query()
            ->whereIn(AnimalLot::FK_ANIMAL_ID, $animalsIds)
            ->orderBy(AnimalLot::FK_ANIMAL_ID, "ASC")
            ->orderBy(AnimalLot::ATTR_ID, "ASC")->get();

        $animalsProcessed = [];

        /**
         * Remove duplicate animal lots
         */
        foreach ($animalsLots as $index => $item) {
            if (!in_array($item->animal_id, $animalsProcessed)) {
                $animalsProcessed[] = $item->animal_id;
                $item->delete();
            }
        }

        return Command::SUCCESS;
    }

}
