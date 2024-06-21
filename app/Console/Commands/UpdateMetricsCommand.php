<?php

namespace App\Console\Commands;

use App\Models\Legacy\Animal;
use App\Models\Metrics\MetricSubscriptions;
use App\Models\Metrics\MetricUsers;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class UpdateMetricsCommand extends Command
{
    const COMMAND = 'metrics:update';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update business metrics for the application';


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->updateReportSubscriptions();
        $this->updateReportUsers();

        return Command::SUCCESS;
    }


    private function updateReportSubscriptions()
    {
        $dateFrom = Carbon::create(date("Y"), date("m"));
        $dateTo = Carbon::create(date("Y"), date("m"), $dateFrom->daysInMonth, 23, 59, 59);

        $usersId = array_map(function ($item) {
            return $item->user_id;
        }, DB::table("suscripcion_usuario")
            ->where("fecha_inicio", "<=", $dateFrom->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })
            ->get("user_id")->all());

        $data = MetricSubscriptions::generateMetrics($dateFrom, $dateTo, $usersId, fn($usersId) => null);
        $metric = MetricSubscriptions::query()->where(MetricUsers::ATTR_DATE, $dateTo->format("Y-m-d"))->first();

        if (is_null($metric)) {
            $metric = new MetricSubscriptions($data);
            $metric->{MetricSubscriptions::ATTR_DATE} = $dateTo->format("Y-m-d");
            return $metric->save();
        }

        return $metric->update($data);
    }

    private function updateReportUsers()
    {
        $dateFrom = Carbon::create(date("Y"), date("m"));
        $dateTo = Carbon::create(date("Y"), date("m"), $dateFrom->daysInMonth, 23, 59, 59);

        $data = MetricUsers::generateMetrics($dateFrom, $dateTo);

        $metric = MetricUsers::query()->where(MetricUsers::ATTR_DATE, $dateTo->format("Y-m-d"))->first();

        if (is_null($metric)) {
            $metric = new MetricUsers($data);
            $metric->{MetricUsers::ATTR_DATE} = $dateTo->format("Y-m-d");
            return $metric->save();
        }

        return $metric->update($data);
    }

}
