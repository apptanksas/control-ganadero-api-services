<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class KVS extends Model
{
    use SoftDeletes;

    const TABLE_NAME = "nv_kvs";

    const ATTR_KEY = "key";
    const ATTR_VALUE = "value";
    const FK_USER_ID = "user_id";

    public $timestamps = true;

    protected $table = self::TABLE_NAME;

    protected $fillable = [self::ATTR_KEY, self::FK_USER_ID, self::ATTR_VALUE];
}
