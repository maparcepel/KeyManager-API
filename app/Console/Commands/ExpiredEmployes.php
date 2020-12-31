<?php

namespace App\Console\Commands;

use App\Expiration_Control;
use App\User;
use App\Mail_History;
use App\Jobs\SendExpirationEmail;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class ExpiredEmployes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expired:employes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestiona y notifica via email los empleados con identificación caducada.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * Coded by: Víctor Castellanos Pérez
     * @return mixed
     */
    public function handle()
    {
        //Buscamos los empleados con identificación caducada en localizaciones dadas de alta.
        $expirations = DB::select('SELECT locations.name AS loc, employes.id, locations.pass_expiration_days, users.id AS userid,users.name, users.surname, users.email, expiration_controls.date, expiration_controls.employe_status_id FROM expiration_controls
        INNER JOIN employes ON employes.id=expiration_controls.employe_id
        INNER JOIN users ON users.id=employes.user_id
        INNER JOIN locations ON locations.id=employes.location_id
        INNER JOIN customers ON customers.id=locations.customer_id
        WHERE customers.cancel_at IS NULL
        AND locations.cancel_at IS NULL
        AND employes.cancel_at IS NULL
        AND expiration_controls.employe_status_id IN (1, 2, 3);');

        //Comprobamos que hayamos obtenido datos antes de continuar.
        if (!empty((array) $expirations)) {
            //Obtenemos la fecha actual
            $fechaActual = now();

            //Obtenemos los dias de envio de recordatorio y cancelacion de emails de la tabla de configuracion.
            $rDays = DB::table('system_configs')->where('key', 'employe_remember_mail_days')->value('value');
            $cDays = DB::table('system_configs')->where('key', 'employe_cancel_mail_days')->value('value');

            foreach ($expirations as $valor) {

                $fPass = date('Y-m-d H:m', strtotime($valor->date));
                $fControl = date('Y-m-d H:m', strtotime(now() . ' - ' . $valor->pass_expiration_days . ' days'));

                if ($fPass < $fControl) {

                    switch ($valor->employe_status_id) {
                        case 1:
                            //Primera caducidad. Enviamos el email de solicitud de cambio de contraseñas.
                            dispatch(new SendExpirationEmail($valor))->delay(Carbon::now()->addSeconds(rand(10, 90)));

                            //Cambiamos el estado del empleado a 2
                            //DB::table('expiration_controls')->where('employe_id', $valor->id)->update(['employe_status_id' => 2]);
                            $expiration_update = Expiration_control::where('employe_id', $valor->id)->update(['employe_status_id' => 2]);

                            //Registramos el envio de email.
                            //DB::table('mail_histories')->insert(['employe_id' => $valor->id, 'date' => now(), 'mail_type_id' => 1]);
                            $mail_histoy = new Mail_History;
                            $mail_histoy->employe_id = $valor->id;
                            $mail_histoy->date = now();
                            $mail_histoy->mail_type_id = 1;
                            $mail_histoy->save();
                            break;
                        case 2:
                            //Segunda caducidad. Se envia el segundo email recordatorio 3.
                            if ($this->checkEmailTiming($rDays, $valor->id, 1)) {
                                dispatch(new SendExpirationEmail($valor))->delay(Carbon::now()->addSeconds(rand(10, 90)));

                                //Cambiamos el estado del empleado a 2
                                //DB::table('expiration_controls')->where('employe_id', $valor->id)->update(['employe_status_id' => 3]);
                                $expiration_update = Expiration_control::where('employe_id', $valor->id)->update(['employe_status_id' => 3]);

                                //Registramos el envio de email.
                                //DB::table('mail_histories')->insert(['employe_id' => $valor->id, 'date' => now(), 'mail_type_id' => 2]);
                                $mail_histoy = new Mail_History;
                                $mail_histoy->employe_id = $valor->id;
                                $mail_histoy->date = now();
                                $mail_histoy->mail_type_id = 2;
                                $mail_histoy->save();
                            }
                            break;
                        case 3:
                            //Anulado. Se envia email indicando la no validez de las contraseñas y se anula acceso web.
                            if ($this->checkEmailTiming($cDays, $valor->id, 2)) {
                                dispatch(new SendExpirationEmail($valor))->delay(Carbon::now()->addSeconds(rand(10, 90)));

                                //Cambiamos el estado del control a 3
                                //DB::table('expiration_controls')->where('employe_id', $valor->id)->update(['employe_status_id' => 4]);
                                $expiration_update = Expiration_control::where('employe_id', $valor->id)->update(['employe_status_id' => 4]);

                                //Bloqueamos la cuenta de usuario.
                                //DB::table('users')->where('id', $valor->userid)->update(['locked' => 1]);
                                $user_update = User::where('id', $valor->userid)->update(['locked' => 1]);

                                //Registramos el envio de email.
                                //DB::table('mail_histories')->insert(['employe_id' => $valor->id, 'date' => now(), 'mail_type_id' => 5]);
                                $mail_histoy = new Mail_History;
                                $mail_histoy->employe_id = $valor->id;
                                $mail_histoy->date = now();
                                $mail_histoy->mail_type_id = 5;
                                $mail_histoy->save();
                            }
                            break;
                    }
                }
            }
        }
    }

    //Función que comprueba si el último email enviado de cambio de claves ha superado el periodo de gracia indicado.
    private function checkEmailTiming($days, $employe, $mailType)
    {
        $data = DB::table('mail_histories')->where([['employe_id', '=', $employe], ['mail_type_id', '=', $mailType]])->orderBy('date', 'desc')->first();

        if (empty((array) $data)) {
            return false;
        } else {
            $fMail = date('Y-m-d H:m', strtotime($data->date));
            $fControl = date('Y-m-d H:m', strtotime(now() . ' - ' . $days . ' days'));
            if ($fMail < $fControl) {
                return true;
            } else {
                return false;
            }
        }

    }
}
