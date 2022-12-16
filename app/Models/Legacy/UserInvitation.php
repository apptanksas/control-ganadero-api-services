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

    protected $table = self::TABLE_NAME;
}
