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
        //$content = str_replace("</twig>", "}}", preg_replace("/\<twig macro=\"([^\"]+)\"\>[^\<]*/", '{{$1', $content));
        $content = str_replace("</twig>", "", preg_replace("/\<twig macro=\"([^\"]+)\"\>[^\<]*/", '$1', $content));
        $loader = new \Twig_Loader_Array([
            'subject' => str_replace('{{', '', str_replace('}}', '', $subject)),
            'signature' => str_replace('{{', '', str_replace('}}', '', $signature)),
            'content' => $content
        ]);
        $twig = new \Twig_Environment($loader);
        $this->subject = $twig->render("subject", $data);
        $this->signature = $twig->render("signature", $data);
        $this->content = $twig->render("content", $data);
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
        return $this->html($this->content);
    }
}
