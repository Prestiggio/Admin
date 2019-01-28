<?php

namespace Ry\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\View;
use Ry\Profile\Models\NotificationTemplate;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Support\Facades\Storage;

class EventCaught extends Mailable
{
    use Queueable, SerializesModels;
    
    private $data, $template;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $data)
    {
        $this->template = $template;
        $this->data = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        //get the template file
        $this->from("no-reply@".env('APP_DOMAIN'), $this->template->arinjections['signature']);
        $this->subject($this->template->arinjections['subject']);
        list($to, $payload, $password) = $this->data;
        $loader = new \Twig_Loader_Array([
            "email" => Storage::disk('local')->get($this->template->medias()->first()->path)
        ]);
        $twig = new \Twig_Environment($loader);
        return $this->html($twig->render("email", ["data" => $payload]));
    }
}
