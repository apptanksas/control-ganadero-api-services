<?php

namespace App\Models\Metrics;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class MetricSubscriptions extends BaseModel
{
    const TABLE_NAME = "metric_subscriptions";

    const ATTR_DATE = "date";

    const ATTR_TOTAL_SUBSCRIPTIONS = "total_subscriptions";

    const ATTR_SUBSCRIPTIONS_BY_NEW_USERS = "subscriptions_by_new_users";

    const ATTR_EXPIRATIONS = "expirations";

    protected $table = self::TABLE_NAME;
    protected $fillable = [self::ATTR_DATE, self::ATTR_TOTAL_SUBSCRIPTIONS, self::ATTR_SUBSCRIPTIONS_BY_NEW_USERS, self::ATTR_EXPIRATIONS];

    protected $primaryKey = self::ATTR_DATE;


    public static function generateMetrics(Carbon $dateFrom, Carbon $dateTo, array $usersIdInCache, callable $onUpdateUsersIdCache)
    {
        $usersId = array_map(function ($item) {
            return $item->user_id;
        }, DB::table("suscripcion_usuario")
            ->where("fecha_inicio", ">=", $dateFrom->format("Y-m-d H:i:s"))
            ->where("fecha_inicio", "<=", $dateTo->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })
            ->get("user_id")->all());

        $usersIdExpiration = array_map(function ($item) {
            return $item->user_id;
        }, DB::table("suscripcion_usuario")
            ->where("fecha_fin", ">=", $dateFrom->format("Y-m-d H:i:s"))
            ->where("fecha_fin", "<=", $dateTo->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })
            ->get("user_id")->all());

        $countSubscriptions = count($usersId);
        $countExpirations = count($usersIdExpiration);

        $countNewSubscriptions = count(array_diff($usersId, $usersIdInCache));
        $usersIdInCache = array_unique(array_merge($usersIdInCache, $usersId));

        $onUpdateUsersIdCache($usersIdInCache);

        return [
            MetricSubscriptions::ATTR_DATE => $dateTo->format("Y-m-d"),
            self::ATTR_TOTAL_SUBSCRIPTIONS => $countSubscriptions,
            self::ATTR_SUBSCRIPTIONS_BY_NEW_USERS => $countNewSubscriptions,
            self::ATTR_EXPIRATIONS => $countExpirations
        ];

    }
}
