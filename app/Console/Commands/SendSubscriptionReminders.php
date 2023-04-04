<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use onesignal\client\api\DefaultApi;
use onesignal\client\Configuration;
use onesignal\client\model\Notification;
use onesignal\client\model\StringMap;

class SendSubscriptionReminders extends Command
{

    const ONE_SIGNAL_USER_KEY = "YTYyYmUwMTYtOWI4Ni00YjZjLWIzOTAtMGJiOTEyNWE3OGI2";
    const ONE_SIGNAL_APP_ID = "65932fd5-079b-48d9-83e6-47336a4c427d";
    const ONE_SIGNAL_API_KEY = "ODk0MGQyZDAtY2NmZi00NGJhLWJmZjYtZWRlMzgyNDcxZTg1";

    const COMMAND = 'notification:reminders';

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
    protected $description = 'Send reminder notifications';

    private DefaultApi $oneSignal;

    function __construct()
    {
        parent::__construct();

        $config = Configuration::getDefaultConfiguration()->setAppKeyToken(self::ONE_SIGNAL_API_KEY)->setUserKeyToken(self::ONE_SIGNAL_USER_KEY);

        $this->oneSignal = new DefaultApi(new Client(), $config);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {

        $usersId = [];
        try {
            // Un mes antes del vencimiento
            $usersId = array_merge($usersId, $this->sendNotificationWithOneMonthPrevious());
            // Una semana antes del vencimiento
            $usersId = array_merge($usersId, $this->sendNotificationWithOneWeekPrevious());
            // Un dia antes del vencimiento
            $usersId = array_merge($usersId, $this->sendNotificationWithOneDayPrevious());
            // Dia del vencimiento
            $usersId = array_merge($usersId, $this->sendNotificationToCurrentDayExpired());
            // Una semana despues del vencimiento
            $usersId = array_merge($usersId, $this->sendNotificationWithOneWeekExpired());
            // Un mes despues del vencimiento
            $usersId = array_merge($usersId, $this->sendNotificationWithOneMonthExpired());
        } catch (\Exception $e) {
            $this->error($e->getMessage());
            return Command::FAILURE;
        }

        $this->info(count($usersId) . " notificaciones de recordatorio enviadas.");
        $this->info("Users ID: " . join(", ", $usersId));

        return Command::SUCCESS;
    }

    private function sendNotificationWithOneMonthPrevious()
    {
        $now = Carbon::now();
        $titleNotification = 'Tu suscripción a Control Ganadero vence en 30 días';
        $messageNotification = "Te invitamos a renovarla antes de su vencimiento. No pierdas el acceso a la información de tus animales.";

        $dateEndSubscriptionInitial = Carbon::create($now->year, $now->month, $now->day, 0, 0, 0)->addMonth();
        $dateEndSubscriptionEnd = Carbon::create($now->year, $now->month, $now->day, 23, 59, 59)->addMonth();
        $usersId = [];

        $result = DB::table("suscripcion_usuario")
            ->where("fecha_fin", ">=", $dateEndSubscriptionInitial->format("Y-m-d H:i:s"))
            ->where("fecha_fin", "<=", $dateEndSubscriptionEnd->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })->get("user_id");

        foreach ($result as $item) {
            $usersId[] = strval($item->user_id);
        }


        return $this->sendNotification($titleNotification, $messageNotification, $usersId);
    }

    private function sendNotificationWithOneWeekPrevious()
    {
        $now = Carbon::now();
        $titleNotification = 'Tu suscripción a Control Ganadero vence en 7 días';
        $messageNotification = "El éxito en la productividad está en tener sus animales al día, al igual que sus inventarios. Renueve su membresía próxima a vencer.";

        $dateEndSubscriptionInitial = Carbon::create($now->year, $now->month, $now->day, 0, 0, 0)->addWeek();
        $dateEndSubscriptionEnd = Carbon::create($now->year, $now->month, $now->day, 23, 59, 59)->addWeek();
        $usersId = [];

        $result = DB::table("suscripcion_usuario")
            ->where("fecha_fin", ">=", $dateEndSubscriptionInitial->format("Y-m-d H:i:s"))
            ->where("fecha_fin", "<=", $dateEndSubscriptionEnd->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })->get("user_id");

        foreach ($result as $item) {
            $usersId[] = strval($item->user_id);
        }

        return $this->sendNotification($titleNotification, $messageNotification, $usersId);
    }

    private function sendNotificationWithOneDayPrevious()
    {
        $now = Carbon::now();
        $titleNotification = 'Tu suscripción a Control Ganadero vence mañana';
        $messageNotification = "Te invitamos a renovarla de inmediato para no perder el acceso a la información de tus animales. ¡Pulsa aquí para renovarla!";

        $dateEndSubscriptionInitial = Carbon::create($now->year, $now->month, $now->day, 0, 0, 0)->addDay();
        $dateEndSubscriptionEnd = Carbon::create($now->year, $now->month, $now->day, 23, 59, 59)->addDay();
        $usersId = [];

        $result = DB::table("suscripcion_usuario")
            ->where("fecha_fin", ">=", $dateEndSubscriptionInitial->format("Y-m-d H:i:s"))
            ->where("fecha_fin", "<=", $dateEndSubscriptionEnd->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })->get("user_id");

        foreach ($result as $item) {
            $usersId[] = strval($item->user_id);
        }

        return $this->sendNotification($titleNotification, $messageNotification, $usersId);
    }

    private function sendNotificationToCurrentDayExpired()
    {
        $now = Carbon::now();
        $titleNotification = 'Tu suscripción a Control Ganadero vence hoy';
        $messageNotification = "No pierdas el acceso a tus animales. Renueva de inmediato tu suscripción. ¡Pulsa aquí para renovarla!";

        $dateEndSubscriptionInitial = Carbon::create($now->year, $now->month, $now->day, 0, 0, 0);
        $dateEndSubscriptionEnd = Carbon::create($now->year, $now->month, $now->day, 23, 59, 59);
        $usersId = [];

        $result = DB::table("suscripcion_usuario")
            ->where("fecha_fin", ">=", $dateEndSubscriptionInitial->format("Y-m-d H:i:s"))
            ->where("fecha_fin", "<=", $dateEndSubscriptionEnd->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })->get("user_id");

        foreach ($result as $item) {
            $usersId[] = strval($item->user_id);
        }

        return $this->sendNotification($titleNotification, $messageNotification, $usersId);
    }

    private function sendNotificationWithOneWeekExpired()
    {
        $now = Carbon::now();
        $titleNotification = 'Tu suscripción a Control Ganadero se encuentra vencida';
        $messageNotification = "Vuelve a disfrutar del acceso sin restriciones a la mejor aplicación de ganaderia. ¡Pulsa aquí para renovar tu suscripción!";

        $dateEndSubscriptionInitial = Carbon::create($now->year, $now->month, $now->day, 0, 0, 0)->subWeek();
        $dateEndSubscriptionEnd = Carbon::create($now->year, $now->month, $now->day, 23, 59, 59)->subWeek();

        $usersId = [];

        $result = DB::table("suscripcion_usuario")
            ->where("fecha_fin", ">=", $dateEndSubscriptionInitial->format("Y-m-d H:i:s"))
            ->where("fecha_fin", "<=", $dateEndSubscriptionEnd->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })->get("user_id");

        foreach ($result as $item) {
            $usersId[] = strval($item->user_id);
        }

        return $this->sendNotification($titleNotification, $messageNotification, $usersId);
    }

    private function sendNotificationWithOneMonthExpired()
    {
        $now = Carbon::now();
        $titleNotification = 'Tu suscripción a Control Ganadero se encuentra vencida';
        $messageNotification = "Vuelve a disfrutar del acceso sin restriciones a la mejor aplicación de ganaderia. ¡Pulsa aquí para renovar tu suscripción!";

        $dateEndSubscriptionInitial = Carbon::create($now->year, $now->month, $now->day, 0, 0, 0)->subMonth();
        $dateEndSubscriptionEnd = Carbon::create($now->year, $now->month, $now->day, 23, 59, 59)->subMonth();

        $usersId = [];

        $result = DB::table("suscripcion_usuario")
            ->where("fecha_fin", ">=", $dateEndSubscriptionInitial->format("Y-m-d H:i:s"))
            ->where("fecha_fin", "<=", $dateEndSubscriptionEnd->format("Y-m-d H:i:s"))
            ->where(function (Builder $builder) {
                return $builder->where("status", "A")->orWhere("status", "M");
            })->get("user_id");

        foreach ($result as $item) {
            $usersId[] = strval($item->user_id);
        }

        return $this->sendNotification($titleNotification, $messageNotification, $usersId);
    }

    private function sendNotification($title, $message, array $usersId): array
    {
        if (empty($usersId)) {
            return [];
        }

        $notification = $this->buildNotification($title, $message, $usersId);

        try {
            $this->oneSignal->createNotification($notification);
            return $usersId;
        } catch (\Exception $e) {
            $this->error("[$this->signature] Error: " . $e->getMessage());
        }
        throw new \Exception("No se pudo enviar las notificaciones");
    }

    private function buildNotification($name, $message, array $usersId): Notification
    {
        $title = new StringMap();
        $title->setEn($name);

        $content = new StringMap();
        $content->setEn($message);

        $notification = new Notification();
        $notification->setAppId(self::ONE_SIGNAL_APP_ID);
        $notification->setHeadings($title);
        $notification->setContents($content);
        $notification->setIncludeExternalUserIds($usersId);
        $notification->setUrl("https://www.controlganadero.com.co/membresias");

        return $notification;
    }

}
