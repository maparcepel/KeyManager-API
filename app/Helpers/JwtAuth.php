<?php
namespace App\Helpers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\DB;
use App\User;
use App\AccessHistory;
use App\UserType;
use App\PasswordHistory;

class JwtAuth{

    public $key;

    public function __construct(){
        $this->key = 'esto_es_una_clave_666';
        define("MAX_INTENTOS", 3);
    }

    public function signup($email, $password, $getToken = null){

    //Buscar si existe el usuario email

        $user = User::where([
            'email' => $email,
            //'web_password' => $password
        ])->first();

    //Comprobar es correcto(objeto)

            $signup = false;
            if(is_object($user)){
                //Compruebo si el usuario ha sido dado de baja
                if($user->cancel_at != null){
                    $error = 'Su cuenta ha sido dada de baja.';
                }elseif($user->locked){ //Revisa si la cuenta ha sido bloqueada
                    //Lanzamos el error correspondiente si se trata de maximos intentos o pendiente de validar
                    $validation = PasswordHistory::where('user_id', $user->id)->first();
                    if($validation->status_type_id==1){
                        $error = 'Su cuenta aún no ha sido validada. Siga el link que se le ha enviado por email para validarla.';
                    }else{
                        $error = 'Su cuenta ha sido bloqueada tras superar el máximo de intentos. Contacte con el administrador.';
                    }
                    
                    
                //comprobar password
                }elseif($user->web_password == $password){
                    $signup = true;
                    $reset_intentos = ['attempts' => 0];
                    User::where('email', $user->email)->update($reset_intentos);

                //Password erronea
                }else{
                    
                    $intentos = $user->attempts;
                    $intentos++;

                    if($intentos >= MAX_INTENTOS){
                        $error = 'Contraseña incorrecta. Máximo de intentos superado. Contacte con el administrador.';
                        $bloqueado = ['ATTEMPTS' => MAX_INTENTOS, 'locked' => true];
                        User::where('email', $user->email)->update($bloqueado);
                    }else{
                        $array_intentos = ['attempts' => $intentos];  
                        User::where('email', $user->email)->update($array_intentos);
                        if($intentos == MAX_INTENTOS-1){
                            $error = 'Contraseña incorrecta. Queda ' . (MAX_INTENTOS-$intentos) . ' intento.';
                        }else{
                            $error = 'Contraseña incorrecta. Quedan ' . (MAX_INTENTOS-$intentos) . ' intentos.';
                        }
                        
                    }
                    
                }

            }else{
                $error = 'No existe ningún usuario con este email.';
            }
            

    

            if($signup){
               
                //Obtener IP
                $ip = \Request::ip();
                
                 //Registrar acceso
                $accessHistory = new AccessHistory();
                $accessHistory->user_id = $user->id;
                $accessHistory->access_ip = $ip;
                $accessHistory->date = now();
                $accessHistory->save();

                //Generar token con los datos del usuario en DB
                $userType = $user->userType->type_name;
                $token = array(
                    'sub'       =>  $user->id,
                    'email'     =>  $user->email,
                    'name'      =>  $user->name,
                    'surname'   =>  $user->surname,
                    'userType'  =>  $userType,  
                    'iat'       =>  time(),
                    'exp'       =>  time() + (7 * 24 * 60 * 60)
                );
                $jwt = JWT::encode($token, $this->key, 'HS256');
                $decoded = JWT::decode($jwt, $this->key, ['HS256']);
            //Devolver los datos decodificados o el token 

                if(is_null($getToken)){
                    
                    $data = array(
                        "status" => true,
                        "message" => "loginUserOk",
                        "response" => array(
                            'token'     => $jwt,
                            'email'     =>  $user->email,
                            'name'      =>  $user->name,
                            'surname'   =>  $user->surname,
                            'user_type'  =>  $userType,
                            'phone'     =>  $user->phone,
                            'dni'       =>  $user->dni
                            )
                    );
                }else{
                    $data = $decoded;
                }

            }else{
                $data = array(
                    "status" => false,
                    "message" => "loginUserError",
                    "response" => array('error' => $error)
                );
                

            }

        return $data;
    }

    //------------------------CHECKTOKEN--------------------------------------

    public function checkToken($jwt, $getIdentity = false){
        $auth = false;

        try{
            $jwt = str_replace('"', '', $jwt);
           
            $decoded = JWt::decode($jwt, $this->key, ['HS256']);

        }catch(\UnexpectedValueException $e){
            $auth = false;
        }catch(\DomainException $e){
            $auth = false; 
        }

        if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){
            $auth = true;
        }else{
            $auth = false;
        }

        if($getIdentity && $auth ==true){
            return $decoded;
        }
        return $auth;
    }

}
