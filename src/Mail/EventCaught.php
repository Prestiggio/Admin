<?php

namespace Ry\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\View;
use Ry\Centrale\SiteScope;
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
        $this->from("no-reply@".env('APP_DOMAIN'), isset($this->template->arinjections['signature']) ? $this->template->arinjections['signature'] : env('APP_DOMAIN'));
        $this->subject(isset($this->template->arinjections['subject']) ? $this->template->arinjections['subject'] : '');
        list($to, $payload) = $this->data;
        $site = app(SiteScope::class)->getSite();
        if(!$site->nsetup['emailing']) {
            $this->to = [['address' => env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com'), 'name' => 'Default recipient']];
        }
        $loader = new \Twig_Loader_Array([
            "email" => Storage::disk('local')->get($this->template->medias()->first()->path)
        ]);
        $twig = new \Twig_Environment($loader);
        return $this->html($twig->render("email", $payload));
    }
}
