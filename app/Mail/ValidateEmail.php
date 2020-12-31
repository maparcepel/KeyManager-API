<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ValidateEmail extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;
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
        return $this->markdown('webPassChange')->with([
            'name' => $this->data['name'] . ' ' . $this->data['surname'],
            'link' => $this->data['link'],
            'email' => $this->data['email'],
            'web_password'=>$this->data['web_password']
        ])->subject('Notificación de validación de contraseña.');
    }
}
