<?php


namespace App\Models;


class AnimalLot extends BaseModel
{
    const TABLE_NAME = "nv_animal_lots";

    const FK_ANIMAL_ID = "animal_id";
    const FK_LOT_ID = "lot_id";

    protected $table = self::TABLE_NAME;

    public $timestamps = false;
}
