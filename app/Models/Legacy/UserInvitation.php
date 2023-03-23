<?php


namespace App\Models\Legacy;

use App\Models\BaseModel;

/**
 * Class UserInvitation
 * @package App\Models
 * @deprecated
 */
class UserInvitation extends BaseModel
{
    const TABLE_NAME = "invitacion_usuario";

    const ATTR_EMAIL = "email";
    const ATTR_USER_TYPE_ID = "tipo_usuario_id";

    const FK_USER_ID = "user_id";
    const FK_FARM_ID = "finca_id";

    protected $table = self::TABLE_NAME;

    function getUserId()
    {
        return $this->{self::FK_USER_ID};
    }

    function getFarmId(){
        return $this->{self::FK_FARM_ID};
    }
}
