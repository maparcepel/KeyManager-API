<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use App\Mail\ExpiredPassMail;

class SendExpiredPassMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    
    protected $data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($data)
    {
        $this->onQueue('webpasswords');
        $this->data=$data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $email=new ExpiredPassMail($this->data);

        //Mail::to($this->data->email)->subject($asunto)->send($email);

        //La línea correcta seria la de arriba, pero para evitar envios de correos a direcciones que podrian ser verdaderas 
        //aunque hemos utilizado un fake, destinaremos todas a info@keymanager.tk durante las pruebas.
        Mail::to('info@keymanager.tk')->send($email);
    }
}
