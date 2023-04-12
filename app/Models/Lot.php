<?php


namespace App\Models;


class Lot extends BaseModel
{
    const TABLE_NAME = "nv_lots";

    const ATTR_NAME = "name";
    const ATTR_NAME_NORMALIZED = "name_normalized";
    const FK_FARM_ID = "farm_id";

    protected $fillable = [self::ATTR_NAME, self::ATTR_NAME_NORMALIZED, self::FK_FARM_ID];

    protected $table = self::TABLE_NAME;
}
