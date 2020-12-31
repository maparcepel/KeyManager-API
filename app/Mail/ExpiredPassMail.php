<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpiredPassMail extends Mailable
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
        switch ($this->data['status']) {
            case 1:
                $asunto = 'Su contraseña de acceso temporal ha expirado.';
                break;
            case 2:
                $asunto = 'Recordatorio de cambio de contraseña acceso web.';
                break;
            case 3:
                $asunto = 'Su contraseña de acceso web a expirado.';
                break;
        }
        return $this->markdown('expiredPassMail')->with([
            'name' => $this->data['name'] . ' ' . $this->data['surname'],
            'link' => $this->data['link'],
            'type' => $this->data['status'],
            'email' => $this->data['email'],
            'days' => $this->data['days'],
        ])->subject($asunto);
    }
}
