<?php


namespace App\Models\Legacy;


use App\Models\BaseModel;

class Animal extends BaseModel
{
    const TABLE_NAME = "animal";

    const ATTR_LOTE = "lote";

    const FK_FINCA_ID = "finca_id";
    const FK_STATE_REPRODUCTION_ID = "estado_reproductivo_id";
    const FK_STATE_GROWTH_ID = "etapa_desarrollo_id";

    public $timestamps = false;
    protected $table = self::TABLE_NAME;

    function getLote()
    {
        return $this->{self::ATTR_LOTE};
    }

    function getFincaId()
    {
        return $this->{self::FK_FINCA_ID};
    }

}
