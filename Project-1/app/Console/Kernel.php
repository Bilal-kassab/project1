<?php

namespace App\Console;

use App\Events\PushWebNotification;
use App\Http\Controllers\StaticBookController;
use App\Http\Controllers\SendTripReminderController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\Booking;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        // dd("fff");
        // $schedule->call(function () {
        //     $controller = new SendTripReminderController();
        //     $controller->sendStaticTripReminder();
        // })->everyMinute(); // Adjust the frequency as needed
        // $schedule->command('my:schedule')->everySecond();
        $schedule->command('queue:work')->everySecond();
        $schedule->command('queue:restart')->everyFiveMinutes();
        // $schedule->call('App\Http\Controllers\BookingController@store_Admin')->everySecond();
        // $schedule->call('App\Http\Controllers\StaticBookController@sendStaticTripReminder')->everySecond();


    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
