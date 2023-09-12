<?php


namespace App\Models\Legacy;


use App\Models\BaseModel;

class Animal extends BaseModel
{
    const TABLE_NAME = "animal";

    const ATTR_LOTE = "lote";
    const ATTR_CODIGO = "codigo";
    const ATTR_NOMBRE = "nombre";
    const ATTR_FECHA_NACIMIENTO = "fecha_nacimiento";
    const ATTR_IS_MACHO = "macho";
    const ATTR_IN_FINCA = "in_finca";
    const ATTR_ESTADO_SALUD_ID = "estado_salud_id";
    const ATTR_ESTADO_VENTA_ID = "estado_venta_animal_id";


    const FK_FINCA_ID = "finca_id";
    const FK_STATE_REPRODUCTION_ID = "estado_reproductivo_id";
    const FK_STATE_GROWTH_ID = "etapa_desarrollo_id";

    const ESTADO_SALUD_SANA = 1;
    const ESTADO_SALUD_MASTITIS = 2;
    const ESTADO_SALUD_EN_TRATAMIENTO = 3;
    const ESTADO_SALUD_FALLECIDA = 4;

    const ESTADO_VENTA_ANIMAL_EN_VENTA = 1;
    const ESTADO_VENTA_ANIMAL_VENDIDO = 2;


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

    function getCodigo()
    {
        return $this->{self::ATTR_CODIGO};
    }

    function getNombre()
    {
        return $this->{self::ATTR_NOMBRE};
    }

    function getFechaNacimiento()
    {
        return $this->{self::ATTR_FECHA_NACIMIENTO};
    }

    function isMacho()
    {
        return $this->{self::ATTR_IS_MACHO} == 0;
    }

    function isHembra()
    {
        return !$this->isMacho();
    }

}
