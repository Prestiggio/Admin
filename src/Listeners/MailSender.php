<?php

namespace Ry\Admin\Listeners;

use App\Events\rynotify*;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailSender implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  rynotify*  $event
     * @return void
     */
    public function handle(rynotify* $event)
    {
        //
    }
}
