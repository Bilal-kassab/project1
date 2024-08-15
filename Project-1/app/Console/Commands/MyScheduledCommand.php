<?php

namespace App\Console\Commands;

use App\Http\Controllers\SendTripReminderController;
use Illuminate\Console\Command;

class MyScheduledCommand extends Command
{

     protected $signature = 'my:schedule';
    protected $description = 'Run the scheduled function in the controller';
    public function handle()
    {
            $controller = new SendTripReminderController();
            $controller->sendStaticTripReminder();
    }
}
