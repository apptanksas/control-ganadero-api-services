<?php


namespace App\Models;


class AnimalLot extends BaseModel
{
    const TABLE_NAME = "nv_animal_lots";

    const FK_ANIMAL_ID = "animal_id";
    const FK_LOT_ID = "lot_id";

    protected $table = self::TABLE_NAME;

    public $timestamps = false;

    protected $fillable = [self::FK_LOT_ID, self::FK_ANIMAL_ID];

    function getLotId()
    {
        return $this->{self::FK_LOT_ID};
    }

    function getAnimalId()
    {
        return $this->{self::FK_ANIMAL_ID};
    }
}
