<?php

namespace Ry\Admin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Ry\Admin\Mail\EventCaught;
use Ry\Admin\Mail\UserInsertCaught;
use Ry\Profile\Models\NotificationTemplate;

class RyAdminNotifier implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $payload;

    private $eventName;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($eventName, $payload)
    {
        $this->eventName = $eventName;
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $eventName = $this->eventName;
        $templates = NotificationTemplate::whereHas("alerts", function($q)use($eventName){
            $q->whereCode($eventName);
        })
        ->where("channels", "LIKE", '%MailSender%')->get();
        if($templates->count()>0) {
            foreach($templates as $template) {
                Mail::send(new EventCaught($template, $this->payload));
            }
        }
        elseif(preg_match("/^ryadminnotify_insert_/", $eventName)) {
            Mail::send(new UserInsertCaught($this->payload));
        }
    }
}
