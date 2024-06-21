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

class GenerateMetricsCommand extends Command
{
    const COMMAND = 'metrics:generate';

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
    protected $description = 'Generate business metrics for the application';


    private const START_YEAR = 2018;
    private const START_MONTH = 1;

    private Carbon $dateFrom;
    private Carbon $dateTo;


    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->generateReportSubscriptions();
        $this->generateReportUsers();

        return Command::SUCCESS;
    }


    private function generateReportSubscriptions()
    {
        // Remove all records from the table
        MetricSubscriptions::query()->delete();

        $this->setup();
        $usersIdInCache = [];

        while ($this->dateFrom->isBefore($this->getCurrentDateMonth())) {

            $data = MetricSubscriptions::generateMetrics($this->dateFrom, $this->dateTo, $usersIdInCache, function ($usersId) use (&$usersIdInCache) {
                $usersIdInCache = $usersId;
            });

            $metric = new MetricSubscriptions($data);

            $metric->saveOrFail();

            $this->dateFrom->addMonth();
            $this->dateTo->addDays($this->dateFrom->daysInMonth);
        }
    }

    private function generateReportUsers()
    {
        // Remove all records from the table
        MetricUsers::query()->delete();

        $this->setup();

        while ($this->dateFrom->isBefore($this->getCurrentDateMonth())) {

            $data = MetricUsers::generateMetrics($this->dateFrom, $this->dateTo);

            $metric = new MetricUsers($data);

            $metric->saveOrFail();

            $this->dateFrom->addMonth();
            $this->dateTo->addDays($this->dateFrom->daysInMonth);
        }
    }

    private function setup()
    {
        $this->dateFrom = Carbon::create(self::START_YEAR, self::START_MONTH);
        $this->dateTo = Carbon::create(self::START_YEAR, self::START_MONTH, $this->dateFrom->daysInMonth, 23, 59, 59);
    }

    private function getCurrentDateMonth()
    {
        return Carbon::create(date("Y"), date("m"));
    }
}
