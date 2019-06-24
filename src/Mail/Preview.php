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
use Auth;
use Twig\Lexer;

class Preview extends Mailable
{
    use SerializesModels;
    
    private $content, $signature, $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject="Email test", $content="Email test", $signature="Undefined", $data=[])
    {
        $this->subject = $subject;
        $this->content = $content;
        $this->signature = $signature;
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
        $this->from("no-reply@".env('APP_DOMAIN'), $this->signature);
        $site = app("centrale")->getSite();
        if(!$site->nsetup['emailing']) {
            $this->to = [['address' => env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com'), 'name' => 'Default recipient']];
        }
        $loader = new \Twig_Loader_Array([
            "email" => str_replace("</twig>", "}}", str_replace("<twig>", "{{", $this->content))
        ]);
        $twig = new \Twig_Environment($loader);
        return $this->html($twig->render("email", $this->data));
    }
}
