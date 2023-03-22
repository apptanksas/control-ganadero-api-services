<?php


namespace App\Models\Legacy;


use App\Models\BaseModel;

class User extends BaseModel
{
    const TABLE_NAME = "user";

    const ATTR_EMAIL = "email";
    const ATTR_IS_OWNER = "is_owner";

    public $timestamps = false;
    protected $table = self::TABLE_NAME;

    function isOwner(): bool
    {
        return $this->{self::ATTR_IS_OWNER} == 1;
    }
    function getEmail():string{
        return $this->{self::ATTR_EMAIL};
    }
}
