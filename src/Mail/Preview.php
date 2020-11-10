<?php

namespace Ry\Admin\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Ry\Profile\Models\NotificationTemplate;
use Ry\Centrale\Models\Push;
use Twig\Loader\ArrayLoader;
use Twig\Environment;

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
        $loader = new ArrayLoader([
            'subject' => str_replace('{{', '', str_replace('}}', '', $subject)),
            'signature' => str_replace('{{', '', str_replace('}}', '', $signature)),
            'content' => $content
        ]);
        $this->data = $data;
        $twig = new Environment($loader);
        $site = app("centrale")->getSite();
        $twig->addGlobal("site", $site->nsetup);
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
        if(!$site->nsetup['general']['email']) {
            $this->to = [['address' => isset($site->nsetup['contact']['email']) ? $site->nsetup['contact']['email'] : env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com'), 'name' => 'Default recipient']];
        }
        else {
            $user = auth()->user();
            $this->to = [['address' => $user->email, 'name' => $user->name]];
        }
        $data = $this->data;
        $content = $this->content;
        return $this->html($this->content)->withSwiftMessage(function(\Swift_Message $m)use($data,$content){
            $cid = $m->getId();
            $user = auth()->user();
            $push = new Push();
            $push->user_id = $user->id; //@todo change to recipient ID
            $push->object_type = NotificationTemplate::class;
            $push->object_id = isset($data['id'])?$data['id']:0;
            $push->content = $content;
            $push->confirm_reading = false;
            $push->channel = 'email';
            $push->cid = $cid;
            $push->save();
        });
    }
}
