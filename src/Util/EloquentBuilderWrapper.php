<?php

namespace Src\Util;

use App\Models\Legacy\Animal;
use Illuminate\Database\Eloquent\Builder;

class EloquentBuilderWrapper
{
    /**
     * @param Builder $builder
     * @return Builder
     */
    static function buildAnimalsActives($builder): Builder
    {
        return $builder->where(function ($builder) {
            return $builder->whereNull(Animal::ATTR_IN_FINCA)->orWhere(Animal::ATTR_IN_FINCA, 1);
        })
            ->where(function ($builder) {
                return $builder->whereNull(Animal::ATTR_ESTADO_SALUD_ID)->orWhere(Animal::ATTR_ESTADO_SALUD_ID, "!=", Animal::ESTADO_SALUD_FALLECIDA);
            })
            ->where(function ($builder) {
                return $builder->where(Animal::ATTR_ESTADO_VENTA_ID, "!=", Animal::ESTADO_VENTA_ANIMAL_VENDIDO)->orWhereNull(Animal::ATTR_ESTADO_VENTA_ID);
            })->whereNull(Animal::ATTR_FECHA_BAJA);
    }
}
