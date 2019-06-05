<?php

namespace Ry\Admin\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Twig\Loader\ArrayLoader;
use Twig\Environment;

class UserInsertCaught extends Mailable
{
    use Queueable, SerializesModels;
    
    private $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($data)
    {
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
        $this->from("no-reply@".env('APP_DOMAIN'), env('APP_DOMAIN'));
        $this->subject('Création de votre compte ' . env('APP_NAME'));
        list($to, $payload) = $this->data;
        $payload['signature'] = env('APP_NAME');
        $payload['contact_email'] = env('DEBUG_RECIPIENT_EMAIL');
        $site = app("centrale")->getSite();
        if(!$site->nsetup['emailing']) {
            $this->to = [['address' => env('DEBUG_RECIPIENT_EMAIL', 'folojona@gmail.com'), 'name' => 'Default recipient']];
        }
        $loader = new ArrayLoader([
            "email" => file_get_contents(__DIR__.'/../assets/userinsert.twig')
        ]);
        $twig = new Environment($loader);
        return $this->html($twig->render("email", $payload));
    }
}
