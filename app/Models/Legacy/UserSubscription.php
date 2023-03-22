<?php


namespace App\Models\Legacy;


use App\Models\BaseModel;

class UserSubscription extends BaseModel
{
    const TABLE_NAME = "suscripcion_usuario";

    const FK_USER_ID = "user_id";

    public $timestamps = false;
    protected $table = self::TABLE_NAME;
}
