<?php

namespace App\Console\Commands;

use App\Models\Legacy\Animal;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdatePhotoDomainCommand extends Command
{
    const COMMAND = 'migration:domain-photo';

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
    protected $description = 'Update animals photo domain';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        /**
         * Replace the domain of the photos from appcontrolganadero.cuatroochenta.com to mdf.controlganadero.com.co
         *
         * SQL: UPDATE animal SET foto = REPLACE(foto, "appcontrolganadero.cuatroochenta.com","mdf.controlganadero.com.co");
         *
         */

        try {
            Animal::query()->whereNotNull(Animal::ATTR_FOTO)->update([
                Animal::ATTR_FOTO => DB::raw("REPLACE(" . Animal::ATTR_FOTO . ", 'appcontrolganadero.cuatroochenta.com','mdf.controlganadero.com.co')")
            ]);

            Animal::query()->whereNotNull(Animal::ATTR_FOTO)->update([
                Animal::ATTR_FOTO => DB::raw("REPLACE(" . Animal::ATTR_FOTO . ", 'controlganadero.cuatroochenta.com','mdf.controlganadero.com.co')")
            ]);

            Animal::query()->whereNotNull(Animal::ATTR_FOTO)->update([
                Animal::ATTR_FOTO => DB::raw("REPLACE(" . Animal::ATTR_FOTO . ", 'http://','https://')")
            ]);

        }catch (\Exception $e){
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

}
