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
use Illuminate\Support\Facades\App;

class EventCaught extends Mailable
{
    use Queueable, SerializesModels;
    
    private $signature, $content;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($template, $data)
    {
        list($recipient_user, $payload) = $data;
        $site = app("centrale")->getSite();
        $data = $payload;
        $media = $template->medias()->where('title', '=', ($recipient_user->preference && isset($recipient_user->preference->ardata['lang']))?$recipient_user->preference->ardata['lang']:App::getLocale())->first();
        if(!$media)
            $media = $template->medias()->first();
        $setup = json_decode($media->descriptif);
        $content = Storage::disk('local')->get($media->path);
        $content = str_replace("</twig>", "}}", preg_replace("/\<twig macro=\"([^\"]+)\"\>[^\<]*/", '{{$1', $content));
        $loader = new \Twig_Loader_Array([
            'subject' => $setup->subject,
            'signature' => $setup->signature,
            'content' => $content
        ]);
        $twig = new \Twig_Environment($loader);
        $this->subject($twig->render("subject", $data));
        $this->content = $twig->render("content", $data);
        $this->to = $recipient_user;
        if(!$site->nsetup['emailing']) {
            $this->to = [['address' => isset($site->nsetup['contact']['email']) ? $site->nsetup['contact']['email'] : env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com'), 'name' => 'Default recipient']];
        }
        $this->from("no-reply@".env('APP_DOMAIN'), $twig->render("signature", $data));
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->html($this->content);
    }
}
