<?php


namespace App\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    private const START_YEAR = 2018;
    private const START_MONTH = 1;
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

            $count = DB::table("user")->where("fechaalta", ">=", $this->dateFrom->timestamp)->where("fechaalta", "<=", $this->dateTo->timestamp)->count();

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
        $this->setup();


        $totalUsers = 0;
        $output = "";

        $output .= "<style>table, th, td {border:1px solid black;}</style>";

        $output .= "<h1>Nuevas suscripciones</h1>";

        $output .= "<table width='500'>";
        $output .= "<tr><th>Año</th><th>Mes</th><th>Suscripciones</th></tr>";

        while ($this->dateFrom->isBefore($this->getCurrentDateMonth())) {

            $count = DB::table("suscripcion_usuario")
                ->where("fecha_inicio", ">=", $this->dateFrom->format("Y-m-d H:i:s"))
                ->where("fecha_inicio", "<=", $this->dateTo->format("Y-m-d H:i:s"))
                ->where(function (Builder $builder) {
                    return $builder->where("status", "A")->orWhere("status", "M");
                })
                ->count();

            $output .= "<tr><td style='text-align: center;'>" . $this->dateFrom->year . "</td><td style='text-align: center;'>" . $this->dateFrom->monthName . "</td><td style='text-align: center;'>" . $count . " </td></tr>";

            $totalUsers += $count;

            $this->dateFrom->addMonth();
            $this->dateTo->addDays($this->dateFrom->daysInMonth);
        }

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

            $query = "select COUNT(*) as count from `suscripcion_usuario` where '" . $this->dateFrom->format("Y-m-d H:i:s") . "' between `fecha_inicio` and `fecha_fin` and '" . $this->dateTo->format("Y-m-d H:i:s") . "' between `fecha_inicio` and `fecha_fin`;";

            $countSubscriptions = DB::select(DB::raw($query))[0]->count;

            $totalUsers = DB::table("user")->where("fechaalta", "<=", $this->dateTo->timestamp)->count();

            $output .= "<tr><td style='text-align: center;'>" . $this->dateFrom->year . "</td><td style='text-align: center;'>" . $this->dateFrom->monthName . "</td><td style='text-align: center;'>" . ($totalUsers - $countSubscriptions) . " </td><td style='text-align: center;'>" . $countSubscriptions . " </td></tr>";


            $this->dateFrom->addMonth();
            $this->dateTo->addDays($this->dateFrom->daysInMonth);
        }

        $output .= "</table>";

        return $output;
    }

}
