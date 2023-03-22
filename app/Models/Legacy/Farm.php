<?php


namespace App\Models\Legacy;


use App\Models\BaseModel;

class Farm extends BaseModel
{
    const TABLE_NAME = "finca";

    const FK_USER_ID = "user_id";

    public $timestamps = false;
    protected $table = self::TABLE_NAME;
}
