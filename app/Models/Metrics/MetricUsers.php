<?php

namespace App\Models\Metrics;

use App\Models\BaseModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MetricUsers extends BaseModel
{
    const TABLE_NAME = "metric_users";

    const ATTR_DATE = "date";

    const ATTR_TOTAL_USERS = "total_users";

    const ATTR_NEW_USERS = "new_users";

    const ATTR_USERS_FREE = "users_free";

    const ATTR_USERS_PAID = "users_paid";

    const ATTR_TOTAL_COUNT_USERS_ACTIVES = "total_count_users_actives";

    const ATTR_COUNT_USERS_ACTIVES_FREE = "count_users_actives_free";

    const ATTR_COUNT_USERS_ACTIVES_PAID = "count_users_actives_paid";


    protected $table = self::TABLE_NAME;
    protected $fillable = [self::ATTR_DATE, self::ATTR_TOTAL_USERS, self::ATTR_NEW_USERS, self::ATTR_USERS_FREE, self::ATTR_USERS_PAID, self::ATTR_TOTAL_COUNT_USERS_ACTIVES, self::ATTR_COUNT_USERS_ACTIVES_FREE, self::ATTR_COUNT_USERS_ACTIVES_PAID];

    protected $primaryKey = self::ATTR_DATE;

    public static function generateMetrics(Carbon $dateFrom, Carbon $dateTo)
    {
        $usersIdsPaid = DB::select(DB::raw("select user_id from `suscripcion_usuario` where '" . $dateFrom->format("Y-m-d H:i:s") . "' between `fecha_inicio` and `fecha_fin` and '" . $dateTo->format("Y-m-d H:i:s") . "' between `fecha_inicio` and `fecha_fin`;"));
        $usersIdsPaid = array_map(function ($item) {
            return $item->user_id;
        }, $usersIdsPaid);

        // Cantidad de total usuarios registrados acumulados hasta el mes iterado
        $countUsers = DB::table("user")->where("fechaalta", "<=", $dateTo->timestamp)->count();
        // Cantidad de usuarios subscritos acumulados durante hasta el mes iterado
        $countUserSubscriber = count($usersIdsPaid);
        // Cantidad de usuarios gratis acumulados hasta el mes iterado
        $countUsersFree = $countUsers - $countUserSubscriber;
        // Cantidad de usuarios nuevos en el mes iterado
        $newUsers = DB::table("user")->where("fechaalta", ">=", $dateFrom->timestamp)->where("fechaalta", "<=", $dateTo->timestamp)->count();
        // Cantidad de usuarios activos en el mes iterado
        // SELECT COUNT(DISTINCT(user_id)) FROM `synchronization` WHERE last_sync >= "2024-06-01" AND last_sync <="2024-06-30"
        $totalCountUsersActives = DB::table("synchronization")->where("last_sync", ">=", $dateFrom->format("Y-m-d"))->where("last_sync", "<=", $dateTo->format("Y-m-d"))->distinct()->count("user_id");
        // Cantidad de usuarios activos de pago en el mes iterado
        // SELECT COUNT(DISTINCT(user_id)) FROM `synchronization` WHERE last_sync >= "2024-06-01" AND last_sync <="2024-06-30" AND user_id IN (select user_id from `suscripcion_usuario` where "2024-06-01 00:00:00" between `fecha_inicio` and `fecha_fin` and "2024-06-01 23:59:59" between `fecha_inicio` and `fecha_fin`)

        $countUsersActivesPaid = DB::table("synchronization")
            ->where("last_sync", ">=", $dateFrom->format("Y-m-d"))
            ->where("last_sync", "<=", $dateTo->format("Y-m-d"))
            ->whereIn("user_id", $usersIdsPaid)->distinct()->count("user_id");

        // Cantidad de usuarios activos gratis en el mes iterado
        $countUsersActivesFree = $totalCountUsersActives - $countUsersActivesPaid;

        return [
            self::ATTR_DATE => $dateTo->format("Y-m-d"),
            self::ATTR_TOTAL_USERS => $countUsers,
            self::ATTR_NEW_USERS => $newUsers,
            self::ATTR_USERS_FREE => $countUsersFree,
            self::ATTR_USERS_PAID => $countUserSubscriber,
            self::ATTR_TOTAL_COUNT_USERS_ACTIVES => $totalCountUsersActives,
            self::ATTR_COUNT_USERS_ACTIVES_FREE => $countUsersActivesFree,
            self::ATTR_COUNT_USERS_ACTIVES_PAID => $countUsersActivesPaid
        ];
    }

}
