<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpirationControl extends Mailable
{
    use Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new message instance.
     * Coded by: Víctor Castellanos Pérez
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
        //Obtenemos que asunto corresponde al email que queremos enviar:
        switch ($this->data->employe_status_id) {
            case 1:
                $asunto = 'Solicitud de cambio de seña, contraseña y código del empleado.';
                break;
            case 2:
                $asunto = 'Recordatorio de solicitud pendiente de gestionar.';
                break;
            case 3:
                $asunto = 'Identificaciones de empleado canceladas. Ya no está autorizado.';
                break;
        }

        return $this->markdown('expirationControl')->with([
            'location' => $this->data->loc,
            'name' => $this->data->name . ' ' . $this->data->surname,
            'days' => $this->data->pass_expiration_days,
            'type' => $this->data->employe_status_id,
            'link' => config('app.url')
        ])->subject($asunto);
    }
}
