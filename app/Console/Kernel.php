<?php

namespace App\Console;

use App\Console\Commands\LoadAnimalLotsCommand;
use App\Console\Commands\RemoveDuplicateAnimalLotsCommand;
use App\Console\Commands\SendSubscriptionReminders;
use App\Console\Commands\UpdatePhotoDomainCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        SendSubscriptionReminders::class,
        LoadAnimalLotsCommand::class,
        UpdatePhotoDomainCommand::class,
        RemoveDuplicateAnimalLotsCommand::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(SendSubscriptionReminders::COMMAND)->dailyAt("06:00")->runInBackground();
        $schedule->command(UpdatePhotoDomainCommand::COMMAND)->everyThirtyMinutes()->runInBackground();
        $schedule->command(RemoveDuplicateAnimalLotsCommand::COMMAND)->dailyAt("03:00")->runInBackground();
        $schedule->command(RemoveDuplicateAnimalLotsCommand::COMMAND)->dailyAt("15:00")->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
