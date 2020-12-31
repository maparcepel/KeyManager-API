<?php

namespace App\Http\Controllers;

use App\AccessHistory;
use App\Customer;
use App\Employe;
use App\Jobs\SendValidateEmail;
use App\Location;
use App\PasswordHistory;
use App\PasswordReset;
use App\User;
use App\UserType;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserController extends Controller
{

//-------------------------------REGISTER---------------------------

    public function register(Request $request)
    {

        $token = $request->header('authorization');
        /* var_dump($token);
        die(); */
        $jwtAuth = new \JwtAuth();
        $decoded = $jwtAuth->checkToken($token, true);
        $checkToken = $jwtAuth->checkToken($token);
        //Recoger datos por post

        $json = $request->input('json', null);
        $params = json_decode($json); //objeto
        $params_array = json_decode($json, true); // array para validar

        //Compruebo validez del token
        if (!$checkToken) {
            $data = array(
                'status' => false,
                'message' => 'userRegisterError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.'),
            );

        } elseif (!empty($decoded) && is_object($decoded) && !empty($params) && !empty($params_array)) {

            if ($decoded->userType == 'Sistema') {

                $params_array = array_map('trim', $params_array);

                //Validar datos
                if ($params_array['user_type_id'] == 1) {
                    $validate = \Validator::make($params_array, [

                        'dni' => 'required',
                        'name' => 'required',
                        'surname' => 'required',
                        //comprueba si el usuario ya existe en la tabla users:

                        'email' => 'required|email|unique:users',

                        'user_type_id' => 'required',
                        'phone' => 'required',

                    ]);
                } else {
                    $validate = \Validator::make($params_array, [

                        'dni' => 'required',
                        'name' => 'required',
                        'surname' => 'required',
                        //comprueba si el usuario ya existe en la tabla users:

                        'email' => 'required|email|unique:users',

                        'user_type_id' => 'required',
                        'phone' => 'required',
                        'customer_id' => 'required',
                    ]);
                }

                if ($validate->fails()) {

                    //La validación ha fallado

                    //Convierto el mensaje de error de Laravel a nuestro formato

                    $a = json_encode($validate->errors());
                    $b = json_decode($a);

                    foreach ($b as $clave => $valor) {
                        $error = $b->$clave[0];
                    }

                    $data = array(
                        'status' => false,
                        'message' => 'userRegisterError',
                        'response' => array('error' => str_replace('web ', '', $error)),
                    );

                } else {
                    //Validación pasada correctamente

                    $duplicado = false;
                    if ($params_array['user_type_id'] != 1) { //diferente de usuario sistema

                        //Compruebo si ya existe el usuario vinculado a este cliente

                        $user_duplicados = User::where(['dni' => $params_array['dni']])->get();
                        if (is_object($user_duplicados)) {
                            foreach ($user_duplicados as $user_duplicado) {
                                $customer_duplicado = $user_duplicado->customers()->find($params_array['customer_id']);
                                if (is_object($customer_duplicado)) {
                                    $duplicado = true;
                                }

                            }

                        }
                    }
                    //if($params_array['user_type_id'])

                    if (!$duplicado) {

                        //crear y cifrar  contraseña
                        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';

                        $pin = mt_rand(1000000, 9999999)
                        . $characters[rand(0, strlen($characters) - 1)]
                        . mt_rand(1000000, 9999999)
                            . $characters[rand(0, strlen($characters) - 1)];

                        $random_psw = str_shuffle($pin);

                        $pwd = hash('sha256', $random_psw);

                        //crear el usuario
                        $user = new User();
                        $user->dni = $params_array['dni'];
                        $user->name = $params_array['name'];
                        $user->surname = $params_array['surname'];
                        $user->email = $params_array['email'];
                        $user->web_password = $pwd;
                        $user->user_type_id = $params_array['user_type_id'];
                        $user->phone = $params_array['phone'];
                        $user->register_ip = \Request::ip();
                        $user->locked = 1; //Al ser registro nuevo bloqueamos la cuenta hasta que cambien la contraseña temporal.

                        $user->save();

                        //guardar password en historial
                        $passHistory = new PasswordHistory();
                        $passHistory->user_id = $user->id;
                        $passHistory->password = $user->web_password;
                        $passHistory->status_type_id = 1; // Valor en 1 ya que la contraseña no está caducada.

                        $passHistory->save();

                        if ($params_array['user_type_id'] != 1) { //diferente de usuario sistema

                            //Creo registro en la tabla pivot customer_user
                            $user->customers()->attach($params_array['customer_id']);
                        }

                        //Creo token para cambio de password

                        $validate_token = hash('sha256', mt_rand(1000000, 9999999));

                        //Envío de email solicitando cambio de password
                        $email_data = [
                            'name' => $user->name,
                            'surname' => $user->surname,
                            'email' => $user->email,
                            'web_password' => $random_psw,
                            'link' => config('app.url') . '/password-reset?validate=' . $validate_token,

                        ];

                        //Mail::to($user->email)->send(new WebPassChange($email_data));
                        dispatch(new SendValidateEmail($email_data))->delay(Carbon::now()->addSeconds(rand(10, 90)));

                        $newUser = User::where([
                            'email' => $params_array['email'],
                        ])->first();

                        //Guardo el token para cambio de password con un nuevo registro en password_resets
                        $password_reset = new PasswordReset();
                        $password_reset->user_id = $newUser->id;
                        $password_reset->email = $newUser->email;
                        $password_reset->token = $validate_token;

                        $password_reset->save();

                        $data = array(
                            'status' => true,
                            'message' => 'userRegisterOk',
                            'response' => array('success' => 'El usuario se ha creado correctamente.',
                                'newUserId' => $newUser->id,
                            ),
                        );
                    } else {
                        $data = array(
                            'status' => false,
                            'message' => 'userRegisterError',
                            'response' => array('error' => 'El usuario con dni ' . $params_array['dni'] .
                                ' ya está dado de alta y asignado al cliente con id: ' . $params_array['customer_id']),
                        );
                    }
                }
            } else {
                $data = array(
                    'status' => false,
                    'message' => 'userRegisterError',
                    'response' => array('error' => 'No tiene privilegios para dar de alta usuarios.'),
                );
            }
        } else {

            $data = array(
                'status' => false,
                'message' => 'userRegisterError',
                'response' => array('error' => 'Los datos no se han enviado correctamente.'),
            );

        }

        return response()->json($data);
    }

    //-------------------------------LOGIN---------------------------

    public function login(Request $request)
    {

        $jwtAuth = new \JwtAuth();

        //Recibir datos por post
        $json = $request->input('json', null);
        $params = json_decode($json); //objeto
        $params_array = json_decode($json, true); //array para validaciones

        //Validar datos
        $validate = \Validator::make($params_array, [
            'email' => 'required|email',
            'web_password' => 'required',
        ]);

        if ($validate->fails()) {
            //La validación ha fallado

            //Convierto el vensaje de error de Laravel a nuestro formato
            $a = json_encode($validate->errors());
            $b = json_decode($a);

            foreach ($b as $clave => $valor) {
                $error = $b->$clave[0];
            }

            $signup = array(
                'status' => false,
                'message' => 'loginUserError',
                'response' => array('error' => str_replace('web ', '', $error)),
            );

        } else {
            //Cifrar la contraseña
            $pwd = hash('sha256', $params->web_password);

            //Devolver token o datos
            $signup = $jwtAuth->signup($params->email, $pwd);

            if (!empty($params->getToken)) {
                $signup = $jwtAuth->signup($params->email, $pwd, true);
            }
        }

        return json_encode($signup);

    }

    //-------------------------------UPDATE---------------------------

    public function update(Request $request)
    {

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if (!$checkToken) {
            $data = array(
                'status' => false,
                'message' => 'updateError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.'),
            );

        } else {
            $decoded = $jwtAuth->checkToken($token, true);
            if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {

                $user_type = $decoded->userType;
                if ($user_type == 'Sistema') {

                    $json = $request->input('json', null);
                    $params_array = json_decode($json, true);

                    //Validar los datos
                    $user_id = $params_array['user_id'];
                    $nuevo_customer_id = $params_array['customer_id'];
                    $validate = \Validator::make($params_array, [
                        'name' => 'required',
                        'surname' => 'required',
                        'dni' => 'required',
                        'email' => 'email',
                        'phone' => 'required',
                        'user_type_id' => 'required',
                    ]);

                //Validar los datos
                $user_id = $params_array['user_id'];
                $nuevo_customer_id = $params_array['customer_id'];
                $validate = \Validator::make($params_array, [
                    'name'      => 'required',
                    'surname'   => 'required',
                    'dni'       => 'required',
                    'email'     => 'required|email|unique:users,email,'.$user_id,
                    'phone'     => 'required',
                    'user_type_id'  => 'required'
                ]);

                //Quitar los campos que no quiero actualizar
                    unset($params_array['user_id']);
                    unset($params_array['customer_id']);

                    if ($validate->fails()) {

                        //Convierto el mensaje de error de Laravel a nuestro formato

                        $a = json_encode($validate->errors());
                        $b = json_decode($a);

                        foreach ($b as $clave => $valor) {
                            $error = $b->$clave[0];
                        }

                        $data = array(
                            'status' => false,
                            'message' => 'updateError',
                            'response' => array('error' => $error),
                        );

                    } else {

                        //Compruebo si quiere cambiar de cliente asignado y si puede cambiarlo
                        $user = User::find($user_id);
                        $cliente = $user->customers()->first();

                        if (!is_object($cliente)) {
                            $cliente_id = null;
                        } else {
                            $cliente_id = $cliente->id;
                        }

                        $usuario_actualizable = true;

                        if ($cliente_id != $nuevo_customer_id) {
                            $empleados_del_usuario = Employe::where(['user_id' => $user_id])->first();

                            if (is_object($empleados_del_usuario)) {
                                $usuario_actualizable = false;
                            } else {
                                //actualizo el cliente. Si no hay relación entre usuario y cliente, la creo
                                if ($cliente_id == null) {
                                    $user->customers()->attach($nuevo_customer_id);
                                } else {
                                    $user->customers()->updateExistingPivot($cliente_id, ['customer_id' => $nuevo_customer_id]);
                                }
                            }
                        }
                        if ($usuario_actualizable) {
                            //Actualizar usuario
                            $user_update = User::where('id', $user_id)->update($params_array);

                            //Si es usuario sistema elimino relación con algún cliente
                            if ($user->user_type_id == 1) {
                                $user->customers()->detach();
                            }

                            //Devolver array resultado

                            $data = array(
                                'status' => true,
                                'message' => 'updateOk',
                                'response' => array('success' => 'El usuario ' . $params_array['name'] . ' ' . $params_array['surname'] . ' se ha editado correctamente.'),
                            );
                        } else {
                            $data = array(
                                'status' => false,
                                'message' => 'updateError',
                                'response' => array('error' => 'El usuario ' . $params_array['name'] . ' ' . $params_array['surname'] . ' no puede cambiar de cliente porque ya tiene empleados vinculados. Debe crear un nuevo usuario.'),
                            );
                        }

                    }

                } else {

                    $data = array(
                        'status' => false,
                        'message' => 'updateError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.'),
                    );
                }

            } else {
                $data = array(
                    'status' => false,
                    'message' => 'updateError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.'),
                );
            }

        }

        return json_encode($data);
    }

//-------------------------------CancelRequest---------------------------

    public function CancelRequest(Request $request)
    {
        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $user_id = $params_array['user_id'];

        //Compruebo validez del token
        if (!$checkToken) {
            $data = array(
                'status' => false,
                'message' => 'cancelRequestError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.'),
            );

        } elseif (!empty($user_id)) {

            //obtengo los datos del usuaio
            $user = User::where([
                'id' => $user_id,
            ])->first();

            //Compruebo si ya está de baja
            if ($user->cancel_at != null) {
                $data = array(
                    'status' => false,
                    'message' => 'cancelRequestError',
                    'response' => array('error' => 'El usuario ya se dio de baja anteriormente.'),
                );
                //Compruebo si es una solicitud duplicada
            } elseif ($user->cancel_request != null) {
                $data = array(
                    'status' => false,
                    'message' => 'cancelRequestError',
                    'response' => array('error' => 'Ya se ha solicitado anteriormente la baja de este usuario.'),
                );

            } else {

                //Actualizar usuario
                $cancel_request = ['cancel_request' => now()];
                $user_update = User::where('id', $user_id)->update($cancel_request);

                $data = array(
                    'status' => true,
                    'message' => 'cancelRequestOk',
                    'response' => array('success' => 'Se ha solicitado con éxito la baja del usuario ' . $user->name . ' ' . $user->surname),
                );
            }

        } else {
            $data = array(
                'status' => false,
                'message' => 'cancelRequestError',
                'response' => array('error' => 'Los datos no se han recibido correctamente.'),
            );
        }

        return json_encode($data);

    }

//-------------------------------getAllUsersCancelRequest---------------------------

    public function getAllUsersCancelRequest(Request $request)
    {

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();

        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if (!$checkToken) {
            $data = array(
                'status' => false,
                'message' => 'getAllUsersCancelRequestError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.'),
            );

        } else {

            $decoded = $jwtAuth->checkToken($token, true);
            $userType = $decoded->userType;
            //verifico usuario sistema
            if ($userType == 'Sistema') {
                //Obtengo usuarios de los que se ha solicitado baja
                $users = User::whereNotNull(
                    'cancel_request'
                )->get();

                //Verifico si hay usuarios para dar de baja
                if (!$users->isEmpty()) {
                    $usuarios = array();

                    //Creo un array con las solicitudes
                    foreach ($users as $user) {
                        //Ignora los usuarios que ya están dados de baja
                        if ($user->cancel_at == null) {
                            $usuario['user_id'] = $user->id;
                            $usuario['dni'] = $user->dni;
                            $usuario['name'] = $user->name;
                            $usuario['surname'] = $user->surname;
                            $usuario['phone'] = $user->phone;
                            $usuario['email'] = $user->email;
                            $usuario['user_type'] = $user->userType->type_name;
                            $usuario['locked_account'] = $user->locked == 1 ? true : false;
                            $usuario['cancel_request'] = $user->cancel_request;

                            array_push($usuarios, $usuario);
                        }
                    }

                    $data = array(
                        'status' => true,
                        'message' => 'getAllUsersCancelRequestOk',
                        'response' => $usuarios,
                    );
                } else {
                    $data = array(
                        'status' => false,
                        'message' => 'getAllUsersCancelRequestError',
                        'response' => array('error' => 'No hay usuarios pendientes de baja.'),
                    );
                }

            } else {
                $data = array(
                    'status' => false,
                    'message' => 'getAllUsersCancelRequestError',
                    'response' => array('error' => 'No tiene privilegios para acceder a esta información.'),
                );
            }

        }

        return json_encode($data);
    }

    //-------------------------------cancel---------------------------

    public function cancel(Request $request)
    {

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if (!$checkToken) {
            $data = array(
                'status' => false,
                'message' => 'cancelError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.'),
            );

        } else {
            $decoded = $jwtAuth->checkToken($token, true);
            $userType = $decoded->userType;

            if ($userType == 'Sistema') { //verifico usuario sistema

                $json = $request->input('json', null);
                $params_array = json_decode($json, true);

                //Obtengo el id del usuario a dar de baja
                $user_id = $params_array['user_id'];

                if (!empty($user_id)) {

                    //Obtengo desde la BD todos sus datos
                    $user = User::where([
                        'id' => $user_id,
                    ])->first();

                    //Compruebo si ya se había dado de baja
                    if ($user->cancel_at != null) {
                        $data = array(
                            'status' => false,
                            'message' => 'cancelError',
                            'response' => array('error' => 'El usuario fue dado de baja anteriormente.'),
                        );

                    } else {

                        //Baja
                        $cancel_at = ['cancel_at' => now()];
                        $user_update = User::where('id', $user_id)->update($cancel_at);

                        $data = array(
                            'status' => true,
                            'message' => 'cancelOk',
                            'response' => array('success' => 'Se ha dado de baja al usuario ' . $user->name . ' ' . $user->surname),
                        );
                    }

                } else {
                    $data = array(
                        'status' => false,
                        'message' => 'cancelError',
                        'response' => array('error' => 'Los datos no se han recibido correctamente.'),
                    );
                }

            } else {
                $data = array(
                    'status' => false,
                    'message' => 'cancelError',
                    'response' => array('error' => 'No tiene privilegios para dar de baja usuarios.'),
                );
            }
        }

        return json_encode($data);
    }

    //-------------------------------getAllCustomersAndUsers---------------------------
    public function getAllCustomersAndUsers(Request $request)
    {

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if (!$checkToken) {
            $data = array(
                'status' => false,
                'message' => 'getAllCustomersAndUsersError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.'),
            );

        } else {
            $decoded = $jwtAuth->checkToken($token, true);
            if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {

                $user_type = $decoded->userType;
                if ($user_type == 'Sistema') {

                    $data = array();
                    $array_aux = array();
                    $array_usuarios = array();

                    //Obtener usuarios sin cliente asignado
                    $no_customer_users = User::doesntHave('Customers')->get();
                    $array_cliente = array();
                    $array_cliente['name'] = '** Administradores del sistema';
                    $array_cliente['customer_id'] = null;

                    foreach ($no_customer_users as $no_customer_user) {
                        $array_usuario = array();
                        $array_usuario['name'] = $no_customer_user->name;
                        $array_usuario['surname'] = $no_customer_user->surname;
                        $array_usuario['user_id'] = $no_customer_user->id;
                        array_push($array_usuarios, $array_usuario);
                    }
                    $array_cliente['users'] = $array_usuarios;
                    array_push($array_aux, $array_cliente);

                    //Creo el array
                    //Obtengo todos los clientes de la BD
                    $clientes = Customer::all();

                    //Recorro los clientes
                    foreach ($clientes as $cliente) {
                        $array_cliente = array();
                        $array_cliente['name'] = $cliente->name;
                        $array_cliente['customer_id'] = $cliente->id;

                        //Obtiene los usuarios asociados a este cliente
                        $usuarios = $cliente->users;
                        $array_usuarios = array();

                        foreach ($usuarios as $usuario) {
                            $array_usuario = array();
                            $array_usuario['name'] = $usuario->name;
                            $array_usuario['surname'] = $usuario->surname;
                            $array_usuario['user_id'] = $usuario->id;
                            array_push($array_usuarios, $array_usuario);
                        }
                        $array_cliente['users'] = $array_usuarios;
                        array_push($array_aux, $array_cliente);
                    }

                    $data = array(
                        'status' => true,
                        'message' => 'getAllCustomersAndUsersOk',
                        'response' => $array_aux,
                    );

                } else {

                    $data = array(
                        'status' => false,
                        'message' => 'getAllCustomersAndUsersError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.'),
                    );
                }

            } else {
                $data = array(
                    'status' => false,
                    'message' => 'getAllCustomersAndUsersError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.'),
                );
            }

        }

        return json_encode($data);
    }

//-------------------------------getAllAvailableUsersForLocation---------------------------

    public function getAllAvailableUsersForLocation(Request $request, $location_id)
    {

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if (!$checkToken) {
            $data = array(
                'status' => false,
                'message' => 'getAllAvailableUsersForLocationError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.'),
            );

        } else {
            $decoded = $jwtAuth->checkToken($token, true);
            if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {

                $user_type = $decoded->userType;
                if ($user_type == 'Sistema') {

                    //Id de cliente dueño de esta localización
                    $cliente_id = Location::find($location_id)->customer_id;

                    //Clientes que no son dueños de esta localizacion
                    $no_clientes = Customer::whereNotIn('id', [$cliente_id])->get();

                    //Ids de users asignados a no_clientes
                    $user_ids_de_otros_clientes = array();

                    foreach ($no_clientes as $no_cliente) {

                        foreach ($no_cliente->users as $user) {
                            array_push($user_ids_de_otros_clientes, $user->id);
                        }

                    }

                    //User_ids de empleados que ya están en esta localización
                    $user_ids_en_location = Employe::where('location_id', $location_id)->pluck('user_id')->toArray();

                    //Usuarios que no están asociados a estos empleados
                    $users = User::whereNotIn('id', $user_ids_en_location)->
                        whereNotIn('id', $user_ids_de_otros_clientes)->
                        where(['cancel_at' => null])->get(['id', 'name', 'surname']);

                    foreach ($users as $key => $value) {
                        $users[$key]['user_id'] = $users[$key]['id'];
                        unset($users[$key]['id']);
                    }

                    $data = array(
                        'status' => true,
                        'message' => 'getAllAvailableUsersForLocationsOk',
                        'response' => $users,
                    );

                } else {

                    $data = array(
                        'status' => false,
                        'message' => 'getAllAvailableUsersForLocationError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.'),
                    );
                }

            } else {
                $data = array(
                    'status' => false,
                    'message' => 'getAllAvailableUsersForLocationError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.'),
                );
            }

        }

        return json_encode($data);
    }

    //-------------------------------getUserById---------------------------

    public function getUserById(Request $request, $usuario_id)
    {

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if (!$checkToken) {
            $data = array(
                'status' => false,
                'message' => 'getUserByIdError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.'),
            );

        } else {
            $decoded = $jwtAuth->checkToken($token, true);
            if (!empty($decoded) && is_object($decoded) && isset($decoded->sub)) {

                $user_type = $decoded->userType;
                $user_id = $decoded->sub;

                if ($user_type == 'Sistema') {

                    $usuario = User::where(['id' => $usuario_id])->first();

                    if (is_object($usuario)) {

                        $customer = $usuario->customers()->first();
                        if (is_object($customer)) {
                            $usuario['customer_id'] = $customer->id;
                            $usuario['customer_name'] = $customer->name;
                        } else {
                            $usuario['customer_id'] = null;
                            $usuario['customer_name'] = null;
                        }

                        $accessHistories = $usuario->accessHistory()->get(['access_ip', 'date']);
                        $usuario['access_histories'] = $accessHistories;

                        $data = array(
                            'status' => true,
                            'message' => 'getUserByIdOk',
                            'response' => $usuario,
                        );
                    } else {
                        $data = array(
                            'status' => false,
                            'message' => 'getUserByIdError',
                            'response' => array('error' => 'No existe un usuario con esta id.'),
                        );
                    }

                } elseif ($user_type == 'Cliente') {
                    //obtengo el usuario  que hace la petición
                    $user_cliente = User::where(['id' => $user_id])->first();
                    //Obtengo el cliente al que pertenece
                    $cliente = $user_cliente->customers()->first();

                    if (is_object($cliente)) {
                        //Verifico si este cliente tiene relación con el usuario de la consulta
                        $usuario = $cliente->users()->find($usuario_id);

                        if (is_object($usuario)) {

                            $customer = $usuario->customers()->first();
                            if (is_object($customer)) {
                                $usuario['customer_id'] = $customer->id;
                                $usuario['customer_name'] = $customer->name;
                            } else {
                                $usuario['customer_id'] = null;
                                $usuario['customer_name'] = null;
                            }

                            $accessHistories = $usuario->accessHistory()->get(['access_ip', 'date']);
                            $usuario['access_histories'] = $accessHistories;

                            $data = array(
                                'status' => true,
                                'message' => 'getUserByIdOk',
                                'response' => $usuario,
                            );
                        } else {
                            $data = array(
                                'status' => false,
                                'message' => 'getUserByIdError',
                                'response' => array('error' => 'No tiene privilegios para acceder a esta información.'),
                            );
                        }
                    } else {
                        $data = array(
                            'status' => false,
                            'message' => 'getUserByIdError',
                            'response' => array('error' => 'No tiene privilegios para acceder a esta información.'),
                        );
                    }

                } elseif ($user_type == 'Empleado' && $user_id == $usuario_id) {

                    $usuario = User::where(['id' => $usuario_id])->first();

                    if (is_object($usuario)) {

                        $customer = $usuario->customers()->first();
                        if (is_object($customer)) {
                            $usuario['customer_id'] = $customer->id;
                        } else {
                            $usuario['customer_id'] = null;
                        }

                        $accessHistories = $usuario->accessHistory()->get(['access_ip', 'date']);
                        $usuario['access_histories'] = $accessHistories;

                        $data = array(
                            'status' => true,
                            'message' => 'getUserByIdOk',
                            'response' => $usuario,
                        );
                    } else {
                        $data = array(
                            'status' => false,
                            'message' => 'getUserByIdError',
                            'response' => array('error' => 'No existe un usuario con esta id.'),
                        );
                    }

                } else {

                    $data = array(
                        'status' => false,
                        'message' => 'getUserByIdError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.'),
                    );
                }

            } else {
                $data = array(
                    'status' => false,
                    'message' => 'getUserByIdError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.'),
                );
            }

        }

        return json_encode($data);
    }

    //-------------------------------checkToken---------------------------

    public function checkToken(Request $request)
    {

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        $token = $params_array['token'];
        $token = str_replace('"', '', $token);

        $password_reset = PasswordReset::where(['token' => $token])->first();

        //Compruebo si el token existe
        if (is_object($password_reset)) {

            //Compruebo validez del token
            $limit_date = strtotime($password_reset->created_at . "+2 days");
            $now = strtotime(now());

            if ($limit_date > $now) {

                $user_name = User::where(['id' => $password_reset->user_id])->first()->name;

                $aux_array = array();
                $aux_array['user_id'] = $password_reset->user_id;
                $aux_array['email'] = $password_reset->email;
                $aux_array['name'] = $user_name;

                $data = array(
                    'status' => true,
                    'message' => 'checkTokenOk',
                    'response' => $aux_array,
                );
            } else {

                $data = array(
                    'status' => false,
                    'message' => 'checkTokenError',
                    'response' => array('error' => 'El token ha caducado.'),
                );
            }

        } else {
            $data = array(
                'status' => false,
                'message' => 'checkTokenError',
                'response' => array('error' => 'El token no existe.'),
            );
        }

        return json_encode($data);
    }

    //-------------------------------passwordReset---------------------------

    public function passwordReset(Request $request)
    {

        $json = $request->input('json', null);
        $params_array = json_decode($json, true);

        if ($params_array['old_password'] == null || $params_array['new_password'] == null) {

            $data = array(
                'status' => false,
                'message' => 'passwordResetError',
                'response' => array('error' => 'No puede enviar un campo vacío.'),
            );

        } else {

            $user_id = $params_array['user_id'];
            $user = User::where(['id' => $user_id])->first();

            //Compruebo si el token existe
            if (is_object($user)) {

                $old_password = hash('sha256', $params_array['old_password']);
                $new_password = hash('sha256', $params_array['new_password']);

                //Verifico password actual
                if ($user->web_password == $old_password) {

                    //Compruebo las últimas 3 passwords
                    $duplicated = false;

                    $password_histories = PasswordHistory::where(['user_id' => $user_id])->latest()->take(3)->get();
                    foreach ($password_histories as $password_history) {
                        if ($password_history->password == $new_password) {
                            $duplicated = true;
                        }
                    }

                    if (!$duplicated) {

                        $update_array['web_password'] = $new_password;
                        $user_update = $user->update($update_array);

                        //Pongo status 3 (inválida) a la anterior password
                        $password_history = $password_histories->first();

                        $password_history_old = $password_history->update(['status_type_id' => 4]);

                        //guardar password en historial
                        $passHistory = new PasswordHistory();
                        $passHistory->user_id = $user_id;
                        $passHistory->password = $new_password;
                        $passHistory->status_type_id = 2; // Valor en 2 ya que la contraseña se ha cambiado y es válida.
                        $passHistory->save();

                        //Desbloqueo el usuario.
                        $user_update = User::where('id', $user_id)->update(['locked' => 0]);

                        //Borro token en password_resets
                        $password_reset = PasswordReset::where(['user_id' => $user_id])->first();
                        if (is_object($password_reset)) {
                            $password_reset->forceDelete();
                        }

                        $data = array(
                            'status' => true,
                            'message' => 'passwordResetOk',
                            'response' => array('success' => 'Su password se ha actualizado correctamente.'),
                        );
                    } else {

                        $data = array(
                            'status' => false,
                            'message' => 'passwordResetError',
                            'response' => array('error' => 'Use una password diferente de las 3 últimas.'),
                        );
                    }
                } else {

                    $data = array(
                        'status' => false,
                        'message' => 'passwordResetError',
                        'response' => array('error' => 'La actual password no es correcta.'),
                    );
                }
            } else {
                $data = array(
                    'status' => false,
                    'message' => 'passwordResetError',
                    'response' => array('error' => 'El usuario no existe.'),
                );
            }
        }

        return json_encode($data);
    }
}
