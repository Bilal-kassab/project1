<?php

namespace App\Jobs;

use App\Mail\TestMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public $user,$code;
    public function __construct($user,$code)
    {
        $this->user=$user;
        $this->code=$code;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user=$this->user;
        $code=$this->code;
        Mail::to($user['email'])->send(new TestMail($user,$code));
    }
}
