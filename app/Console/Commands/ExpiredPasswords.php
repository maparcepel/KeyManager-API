<?php

namespace App\Console\Commands;

use App\Jobs\SendExpiredPassMail;
use App\PasswordHistory;
use App\PasswordReset;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class ExpiredPasswords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expired:passwords';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Gestiona y notifica via email los usuarios con contraseña cercana a la caducidad para recordarles cambiarla.';

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
     *
     * @return mixed
     */
    public function handle()
    {
        //Buscamos las contraseñas pendientes de validar.
        $expiredPasswords = DB::select('SELECT password_histories.id AS pwd_id, password_histories.user_id AS users_id, users.name, users.surname, users.email, password_histories.status_type_id, password_histories.created_at FROM password_histories
              INNER JOIN users ON users.id=password_histories.user_id
              WHERE users.cancel_at IS NULL
              AND password_histories.status_type_id IN (1, 2, 3);');

        //Comprobamos que hayamos obtenido datos antes de continuar.
        if (!empty((array) $expiredPasswords)) {
            //Obtenemos los datos de la tabla de configuracion.
            $rDays = DB::table('system_configs')->where('key', 'password_remember_mail_days')->value('value');
            $exDays = DB::table('system_configs')->where('key', 'user_pass_expire_days')->value('value');
            $valDays = DB::table('system_configs')->where('key', 'user_pass_validate_days')->value('value');

            foreach ($expiredPasswords as $valor) {

                $fValidate = Carbon::Parse($valor->created_at)->addDays($valDays); //fecha max. para validar la password
                $fRemember = Carbon::Parse($valor->created_at)->addDays($exDays - $rDays); //fecha recordatorio caducidad password.
                $fInvalid = Carbon::Parse($valor->created_at)->addDays($exDays); //fecha caducidad password.

                //Añadimos el valor de dias de expiracion al objeto para poder pasarlo a posteriori al email.
                switch ($valor->status_type_id) {
                    case 1:
                        //caducidad contraseñas temporales de registro.
                        if (now() > $fValidate) {
                            //Notificamos la invalidez de la contraseña
                            $email_data = [
                                'name' => $valor->name,
                                'surname' => $valor->surname,
                                'email' => $valor->email,
                                'days' => $valDays,
                                'link' => '',
                                'status'=>$valor->status_type_id

                            ];
                            dispatch(new SendExpiredPassMail($email_data))->delay(Carbon::now()->addSeconds(rand(10, 90)));

                            //cambiamos el estado a 3 (invalida)
                            $password_update = PasswordHistory::where('id', $valor->pwd_id)->update(['status_type_id' => 4]);

                            //eliminamos el token
                            $password_reset = PasswordReset::where(['user_id' => $valor->users_id]);
                            if (is_object($password_reset)) {
                                $password_reset->forceDelete();
                            }

                            //bloqueamos el usuario
                            $user_update = User::where('id', $valor->users_id)->update(['locked' => 1]);
                        }

                        break;
                    case 2:
                        //caducidad contraseñas validadas a X horas del limite de tiempo de uso limite de uso.
                        if (now() > $fRemember) {
                            //Creo token para cambio de password

                            $validate_token = hash('sha256', mt_rand(1000000, 9999999));

                            //Envío de email solicitando cambio de password
                            $email_data = [
                                'name' => $valor->name,
                                'surname' => $valor->surname,
                                'email' => $valor->email,
                                'days' => $rDays,
                                'link' => config('app.url') . '/password-reset?renew=' . $validate_token,
                                'status'=>$valor->status_type_id

                            ];

                            dispatch(new SendExpiredPassMail($email_data))->delay(Carbon::now()->addSeconds(rand(10, 90)));

                            $newUser = User::where([
                                'email' => $valor->email,
                            ])->first();

                            //Guardo el token para cambio de password con un nuevo registro en password_resets
                            $password_reset = new PasswordReset();
                            $password_reset->user_id = $newUser->id;
                            $password_reset->email = $newUser->email;
                            $password_reset->token = $validate_token;

                            $password_reset->save();

                            //cambiamos el estado a 3 (expirada)
                            $password_update = PasswordHistory::where('id', $valor->pwd_id)->update(['status_type_id' => 3]);
                        }
                        break;
                    case 3:
                        //caducidad contraseñas validadas que han sobrepasado el tiempo limite de uso.
                        if (now() > $fInvalid) {
                            //Notificamos la invalidez de la contraseña
                            $email_data = [
                                'name' => $valor->name,
                                'surname' => $valor->surname,
                                'email' => $valor->email,
                                'days' => $exDays,
                                'link' => '',
                                'status'=>$valor->status_type_id

                            ];
                            dispatch(new SendExpiredPassMail($email_data))->delay(Carbon::now()->addSeconds(rand(10, 90)));

                            //Cambiamos el estado a 3 (caducada)
                            $password_update = PasswordHistory::where('id', $valor->pwd_id)->update(['status_type_id'=> 4]);

                            //bloqueamos el usuario
                            $user_update = User::where('id', $valor->users_id)->update(['locked'=> 1]);

                            //Borro token en password_resets
                            $password_reset = PasswordReset::where(['user_id' => $valor->user_id])->first();
                            if (is_object($password_reset)) {
                                $password_reset->forceDelete();
                            }
                        }
                        break;
                }
            }
        }
    }
}
