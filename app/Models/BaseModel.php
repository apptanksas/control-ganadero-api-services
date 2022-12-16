<?php


namespace App\Models;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;

abstract class BaseModel extends Model
{
    const TABLE_NAME = null;
    const ATTR_ID = "id";
    const FORMAT_DATETIME = 'Y-m-d H:i:s';

    protected $primaryKey = self::ATTR_ID;

    protected $table = self::TABLE_NAME;

    /**
     * @param $id
     *
     * @throws ModelNotFoundException
     */
    public static function queryById($id): mixed
    {
        /**
         * @var $class BaseModel
         */
        $class = get_called_class();

        return $class::query()->where(self::ATTR_ID, $id)->firstOrFail();
    }

    function getId()
    {
        return $this->{self::ATTR_ID};
    }

    function setId($id)
    {
        $this->{self::ATTR_ID} = $id;
    }

    /**
     * @param $collection Collection|static[]|\Illuminate\Support\Collection
     * @param string|null $modelClass
     * @return array
     */
    public static function collectionQueryToList(array|Collection|\Illuminate\Support\Collection $collection, string $modelClass = null): array
    {
        $output = [];

        foreach ($collection as $item) {
            $model = ($modelClass != null) ? new $modelClass((array)$item) : $item;

            //Set attribute ID
            if (isset($item->{self::ATTR_ID}) && !is_null($item->{self::ATTR_ID})) {
                $model->{self::ATTR_ID} = $item->{self::ATTR_ID};
            }

            $output[] = $model;
        }

        return $output;
    }
}
