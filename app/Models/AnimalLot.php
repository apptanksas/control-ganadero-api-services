<?php


namespace App\Models;


class AnimalLot extends BaseModel
{
    const TABLE_NAME = "nv_animal_lots";

    const ATTR_NAME = "name";

    const FK_ANIMAL_ID = "animal_id";
    const FK_FARM_ID = "farm_id";

    protected $table = self::TABLE_NAME;
}
