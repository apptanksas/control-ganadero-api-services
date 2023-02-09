<?php


namespace App\Http\Controllers;


use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    function getNewUsers()
    {
        $year = 2018;
        $month = 1;

        $dateFrom = Carbon::create($year, $month);
        $dateTo = Carbon::create($year, $month, $dateFrom->daysInMonth);
        $totalUsers = 0;

        print "<style>table, th, td {border:1px solid black;}</style>";

        print "<h1>Registro de usuarios</h1>";

        print "<table width='500'>";
        print "<tr><th>Año</th><th>Mes</th><th>Usuarios Nuevos</th></tr>";
        while ($dateFrom->isBefore(Carbon::create(date("Y"), date("m")))) {

            $count = DB::table("user")->where("fechaalta", ">=", $dateFrom->timestamp)->where("fechaalta", "<=", $dateTo->timestamp)->count();

            print "<tr><td style='text-align: center;'>" . $dateFrom->year . "</td><td style='text-align: center;'>" . $dateFrom->monthName . "</td><td style='text-align: center;'>" . $count . " </td></tr>";

            $totalUsers += $count;

            $dateFrom->addMonth();
            $dateTo->addDays($dateFrom->daysInMonth);
        }

        print "</table>";

        print "\n<br/> <h2>Total Users = $totalUsers</h2>";
    }



}
