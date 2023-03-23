<?php


namespace App\Models\Legacy;


use App\Models\BaseModel;

class UserSubscription extends BaseModel
{
    const TABLE_NAME = "suscripcion_usuario";

    const ATTR_DATE_START = "fecha_inicio";
    const ATTR_DATE_END = "fecha_fin";

    const FK_USER_ID = "user_id";

    public $timestamps = false;
    protected $table = self::TABLE_NAME;
}
