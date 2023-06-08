<?php


namespace App\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    private const START_YEAR = 2018;
    private const START_MONTH = 1;
    private const CACHE_KEY_USERS_ID_SUBSCRIPTION = "users_id_subscription_%s_%s";
    private Carbon $dateFrom;
    private Carbon $dateTo;


    private function setup()
    {
        $this->dateFrom = Carbon::create(self::START_YEAR, self::START_MONTH);
        $this->dateTo = Carbon::create(self::START_YEAR, self::START_MONTH, $this->dateFrom->daysInMonth, 23, 59, 59);
    }

    private function getCurrentDateMonth()
    {
        return Carbon::create(date("Y"), date("m"));
    }

    function getNewUsers()
    {

        $this->setup();

        $totalUsers = 0;
        $output = "";

        $output .= "<style>table, th, td {border:1px solid black;}</style>";

        $output .= "<h1>Registro de usuarios</h1>";

        $output .= "<table width='500'>";
        $output .= "<tr><th>Año</th><th>Mes</th><th>Usuarios Nuevos</th></tr>";

        while ($this->dateFrom->isBefore($this->getCurrentDateMonth())) {

            $dateFrom = $this->dateFrom->format("Y-m-d H:i:s");
            $dateTo = $this->dateTo->format("Y-m-d H:i:s");

            $count = Cache::rememberForever("statistic_get_new_users_$dateFrom" . "_" . $dateTo, function () {
                return DB::table("user")->where("fechaalta", ">=", $this->dateFrom->timestamp)->where("fechaalta", "<=", $this->dateTo->timestamp)->count();
            });

            $output .= "<tr><td style='text-align: center;'>" . $this->dateFrom->year . "</td><td style='text-align: center;'>" . $this->dateFrom->monthName . "</td><td style='text-align: center;'>" . $count . " </td></tr>";

            $totalUsers += $count;

            $this->dateFrom->addMonth();
            $this->dateTo->addDays($this->dateFrom->daysInMonth);
        }

        $output .= "</table>";

        $output .= "\n<br/> <h2>Total nuevos usuarios = $totalUsers</h2>";

        return $output;
    }


    function getNewSubscriptions()
    {

        $a = [1, 2, 3, 4, 5, 6];
        $b = [2, 3];
        //print_r(array_diff($a, $b));
        //print_r(array_unique(array_merge($a,$b)));
        //return;
        $this->setup();

        $totalUsers = 0;
        $output = "";

        $output .= "<style>table, th, td {border:1px solid black;}</style>";

        $output .= "<h1>Suscripciones</h1>";

        $output .= "<table width='800'>";
        $output .= "<tr><th>Año</th><th>Mes</th><th>Suscripciones</th><th>Nuevas Suscripciones</th><th>Renovaciones</th><th>Vencimientos</th><th width='200px'>% Renovación por vencimientos</th></tr>";

        $usersIdInCache = [];

        while ($this->dateFrom->isBefore($this->getCurrentDateMonth())) {

            $dateFrom = $this->dateFrom->format("Y-m-d H:i:s");
            $dateTo = $this->dateTo->format("Y-m-d H:i:s");


            $usersId = Cache::rememberForever("statistic_get_new_subscriptions_$dateFrom" . "_" . $dateTo, function () {
                return array_map(function ($item) {
                    return $item->user_id;
                }, DB::table("suscripcion_usuario")
                    ->where("fecha_inicio", ">=", $this->dateFrom->format("Y-m-d H:i:s"))
                    ->where("fecha_inicio", "<=", $this->dateTo->format("Y-m-d H:i:s"))
                    ->where(function (Builder $builder) {
                        return $builder->where("status", "A")->orWhere("status", "M");
                    })
                    ->get("user_id")->all());
            });

            $usersIdExpiration = Cache::rememberForever("statistic_get_new_expirations_$dateFrom" . "_" . $dateTo, function () {
                return array_map(function ($item) {
                    return $item->user_id;
                }, DB::table("suscripcion_usuario")
                    ->where("fecha_fin", ">=", $this->dateFrom->format("Y-m-d H:i:s"))
                    ->where("fecha_fin", "<=", $this->dateTo->format("Y-m-d H:i:s"))
                    ->where(function (Builder $builder) {
                        return $builder->where("status", "A")->orWhere("status", "M");
                    })
                    ->get("user_id")->all());
            });

            $countSubscriptions = count($usersId);
            $countExpirations = count($usersIdExpiration);
            $percentageRenovationByExpiration = ($countExpirations == 0) ? 0 : round((count(array_intersect($usersId, $usersIdExpiration)) / $countExpirations) * 100, 2);

            $countNewSubscriptions = count(array_diff($usersId, $usersIdInCache));
            $countRenovations = $countSubscriptions - $countNewSubscriptions;


            $usersIdInCache = array_unique(array_merge($usersIdInCache, $usersId));

            $output .= "<tr><td style='text-align: center;'>" . $this->dateFrom->year . "</td>";
            $output .= "<td style='text-align: center;'>" . $this->dateFrom->monthName . "</td>";
            $output .= "<td style='text-align: center;'>" . $countSubscriptions . " </td>";
            $output .= "<td style='text-align: center;'>" . $countNewSubscriptions . " </td>";
            $output .= "<td style='text-align: center;'>" . $countRenovations . " </td>";
            $output .= "<td style='text-align: center;'>" . $countExpirations . " </td>";
            $output .= "<td style='text-align: center;' width='100px'>" . $percentageRenovationByExpiration . "%</td>";
            $output .= "</tr>";

            $totalUsers += $countSubscriptions;

            $this->dateFrom->addMonth();
            $this->dateTo->addDays($this->dateFrom->daysInMonth);
        }

        $output .= "<tr><th>Año</th><th>Mes</th><th>Suscripciones</th><th>Nuevas Suscripciones</th><th>Renovaciones</th><th>Vencimientos</th><th width='200px'>% Renovación por vencimientos</th></tr>";

        $output .= "</table>";

        $output .= "\n<br/> <h2>Total nuevas suscripciones = $totalUsers</h2>";

        return $output;
    }

    function getUsers()
    {
        $this->setup();

        $output = "";

        $output .= "<style>table, th, td {border:1px solid black;}</style>";

        $output .= "<h1>Crecimiento de usuarios</h1>";

        $output .= "<table width='500'>";
        $output .= "<tr><th>Año</th><th>Mes</th><th>Usuarios Gratis</th><th>Usuarios Pago</th></tr>";

        while ($this->dateFrom->isBefore($this->getCurrentDateMonth())) {

            $dateFrom = $this->dateFrom->format("Y-m-d H:i:s");
            $dateTo = $this->dateTo->format("Y-m-d H:i:s");

            $countSubscriptions = Cache::rememberForever("statistic_get_users_$dateFrom" . "_" . $dateTo, function () {
                $query = "select COUNT(*) as count from `suscripcion_usuario` where '" . $this->dateFrom->format("Y-m-d H:i:s") . "' between `fecha_inicio` and `fecha_fin` and '" . $this->dateTo->format("Y-m-d H:i:s") . "' between `fecha_inicio` and `fecha_fin`;";
                return DB::select(DB::raw($query))[0]->count;
            });

            $totalUsers = DB::table("user")->where("fechaalta", "<=", $this->dateTo->timestamp)->count();

            $output .= "<tr><td style='text-align: center;'>" . $this->dateFrom->year . "</td><td style='text-align: center;'>" . $this->dateFrom->monthName . "</td><td style='text-align: center;'>" . ($totalUsers - $countSubscriptions) . " </td><td style='text-align: center;'>" . $countSubscriptions . " </td></tr>";


            $this->dateFrom->addMonth();
            $this->dateTo->addDays($this->dateFrom->daysInMonth);
        }

        $output .= "</table>";

        return $output;
    }


    function getAnimals()
    {

        $this->setup();

        $totalAnimals = 0;
        $output = "";

        $output .= "<style>table, th, td {border:1px solid black;}</style>";

        $output .= "<h1>Registro de animales</h1>";

        $output .= "<table width='500'>";
        $output .= "<tr><th>Año</th><th>Mes</th><th>Nuevos animales</th><th>Cantidad de animales registrados</th></tr>";

        while ($this->dateFrom->isBefore($this->getCurrentDateMonth())) {

            $dateFrom = $this->dateFrom->format("Y-m-d H:i:s");
            $dateTo = $this->dateTo->format("Y-m-d H:i:s");

            $newAnimals = Cache::rememberForever("statistic_get_new_animals_$dateFrom" . "_" . $dateTo, function () {
                return DB::table("animal")->where("fechaalta", ">=", $this->dateFrom->timestamp)->where("fechaalta", "<=", $this->dateTo->timestamp)->whereNull("fechabaja")->count();
            });
            $totalAnimals = Cache::rememberForever("statistic_get_total_animals_$dateFrom" . "_" . $dateTo, function () {
                DB::table("animal")->where("fechaalta", "<=", $this->dateTo->timestamp)->whereNull("fechabaja")->count();
            });
            $output .= "<tr><td style='text-align: center;'>" . $this->dateFrom->year . "</td><td style='text-align: center;'>" . $this->dateFrom->monthName . "</td><td style='text-align: center;'>" . $newAnimals . " </td><td style='text-align: center;'>" . $totalAnimals . " </td></tr>";

            $totalAnimals += $newAnimals;

            $this->dateFrom->addMonth();
            $this->dateTo->addDays($this->dateFrom->daysInMonth);
        }

        $output .= "</table>";

        $output .= "\n<br/> <h2>Total animales registrados = $totalAnimals</h2>";

        return $output;
    }
}
