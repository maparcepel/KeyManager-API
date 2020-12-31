<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Employe;
use App\Location;
use App\User;
use App\Customer;
use App\Helpers\Security;
use Firebase\JWT\JWT;
use App\Expiration_Control;
use App\Employe_Status;

class EmployeeController extends Controller
{
        //-------------------------------getLocationEmployees---------------------------

    public function getLocationEmployees(Request $request, $location_id){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'getLocationEmployeesError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{  
            $decoded = $jwtAuth->checkToken($token, true);
            $userType = $decoded->userType;
            
            if($userType == 'Empleado'){

                $user_id = $decoded->sub;
                $empleado = Employe::where(['location_id'   => $location_id,
                                            'user_id'       => $user_id])->first();
                    
                if(is_object($empleado)){
                    $emp_array = array();
                        //Reviso si está dado de baja (cancel_at)
                        if($empleado->cancel_at == null){
                            $emp['employee_id'] = $empleado->id;
                            $user['user_id'] = $empleado->user->id;
                            $user['dni']     = $empleado->user->dni;
                            $user['name']    = $empleado->user->name;
                            $user['surname'] = $empleado->user->surname;
                            $user['phone']   = $empleado->user->phone;
                            $user['email']   = $empleado->user->email;
                            $user['locked_account']  = $empleado->user->locked == 1?true : false;
            
                            $emp['position_type']       = $empleado->positionType->position_name;
                            $emp['position_type_id']    = $empleado->position_type_id;
                            $emp['call_order']          = $empleado->call_order;
                            $emp['caller_password']     = Security::decrypt($empleado->caller_password);
                            $emp['personal_password']   = Security::decrypt($empleado->personal_password);
                            $emp['panel_order']         = $empleado->panel_order;
                            $emp['panel_code']          = Security::decrypt($empleado->panel_code);
                            $emp['biometric_id']        = $empleado->biometric_id;
                            $emp['employee_status_id']  = $empleado->expirationControl->employe_status_id;

                            $emp['user'] =  $user;
                            array_push($emp_array, $emp);

                            $data = array( 
                                'status'  => true,
                                'message' => 'getLocationEmployeesOk',
                                'response' => $emp_array
                            );
                        }else{
                            $data = array( 
                                'status'  => false,
                                'message' => 'getLocationEmployeesError',
                                'response' => array('error' => 'Su cuenta ha sido dada de baja.')
                            );
                        }

                    
                }else{
                    $data = array( 
                        'status'  => false,
                        'message' => 'getLocationEmployeesError',
                        'response' => array('error' => 'Su perfil no tiene acceso a esta localización.')
                    );
                }
                
            }else{
                //Verifico que la location existe
                $location = Location::where([
                    'id' => $location_id,
                ])->first();
                

                if(is_object($location)){

                    //Obtengo empleados en esta location
                    $empleados = Employe::where([
                        'location_id' =>  $location_id
                    ])->get();

                    $emp_array = array();

                    foreach($empleados as $empleado){
                        
                        //Reviso si está dado de baja (cancel_at)
                        if($empleado->cancel_at == null){
                            $emp['employee_id'] = $empleado->id;
                            $user['user_id'] = $empleado->user->id;
                            $user['dni']     = $empleado->user->dni;
                            $user['name']    = $empleado->user->name;
                            $user['surname'] = $empleado->user->surname;
                            $user['phone']   = $empleado->user->phone;
                            $user['email']   = $empleado->user->email;
                            $user['locked_account']  = $empleado->user->locked == 1?true : false;
            
                            $emp['position_type']   = $empleado->positionType->position_name;
                            $emp['position_type_id']   = $empleado->position_type_id;
                            $emp['call_order']   = $empleado->call_order;
                            //Info sólo disponible para sistema
                            if($userType == 'Sistema'){
                                $emp['caller_password']   = Security::decrypt($empleado->caller_password);

                                $emp['personal_password']   = Security::decrypt($empleado->personal_password);
                            }
                            $emp['panel_order']   = $empleado->panel_order;
                            //Info sólo disponible para sistema
                            if($userType == 'Sistema'){
                                $emp['panel_code']   = Security::decrypt($empleado->panel_code);
                            }
                            $emp['biometric_id']   = $empleado->biometric_id;
                            $emp['employee_status_id']  = $empleado->expirationControl->employe_status_id;

                            $emp['user'] =  $user;
                            array_push($emp_array, $emp);
                        }
                    }
        
                    $data = array( 
                        'status'  => true,
                        'message' => 'getLocationEmployeesOk',
                        'response' => $emp_array
                    );
                }else{
                    $data = array( 
                        'status'  => false,
                        'message' => 'getLocationEmployeesError',
                        'response' => array('error' => 'Esta ubicación no existe.')
                    );
                }
            
            }
        }

        return json_encode($data);    
        
    }

    //-------------------------------REGISTER---------------------------
    public function register(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'getAllCustomersAndUsersError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema'){

                    $json = $request->input('json', null);
                    
                    $params = json_decode($json);   //objeto
                    $params_array = json_decode($json, true);  // array para validar
                    

                    $params_array = array_map('trim', $params_array);

                    //Validar datos

                    $validate = \Validator::make($params_array, [
                        'location_id'       => 'required|integer',
                        'user_id'           => 'required|integer',
                        'position_type_id'  => 'required|integer',
                        'call_order'        => 'required|integer', 
                        'caller_password'   => 'required',
                        'personal_password' => 'required',
                        'panel_order'       => 'required|integer',
                        'panel_code'        => 'required|integer',
                        'biometric_id'      => 'required|integer'

                    ]);

                    if($validate->fails()){

                        //La validación ha fallado
                        
                        //Convierto el mensaje de error de Laravel a nuestro formato
                        $a = json_encode($validate->errors());
                        $b = json_decode($a);
                        foreach($b as $clave => $valor){
                                $error = $b->$clave[0];
                        }
    
    
                        $data = array( 
                            'status'  => false,
                            'message' => 'employeeRegisterError',
                            'response' => array('error' => str_replace('web ', '',$error))
                        );
    
                    }else{

                        $user = User::find($params_array['user_id']);
                        if($user->user_type_id != 1){ 
                            //Compruebo si este usuario ya está en esta ubicación

                            $duplicado = false;

                            $employees = Employe::where([
                                'location_id' => $params_array['location_id']
                            ])->get();

                            foreach($employees as $employee){
                                if($employee->user_id == $params_array['user_id']){
                                    $duplicado = true;
                                    break;
                                }
                            }

                            if($duplicado){

                                $location = Location::find($params_array['location_id']);

                                $data = array( 
                                    'status'  => false,
                                    'message' => 'employeeRegisterError',
                                    'response' => array('error' => 'El usuario ' . $user->name . ' ' . $user->surname . 
                                                    ' ya estaba asignado anteriormente a ' . $location->name)
                                ); 
                            }else{

                                //Validación pasada correctamente
                                //cifrar las contraseñas

                                $caller_password = Security::encrypt($params->caller_password);
                                $personal_password = Security::encrypt($params->personal_password);
                                $panel_code = Security::encrypt($params->panel_code);

            
                                //crear el empleado
                                $empleado = new Employe();
                                $empleado->location_id      = $params_array['location_id'];
                                $empleado->user_id          = $params_array['user_id'];
                                $empleado->position_type_id = $params_array['position_type_id'];
                                $empleado->call_order       = $params_array['call_order'];
                                $empleado->caller_password  = $caller_password;
                                $empleado->personal_password = $personal_password;
                                $empleado->panel_order       = $params_array['panel_order'];
                                $empleado->panel_code        = $panel_code;
                                $empleado->biometric_id      = $params_array['biometric_id'];
            
                                $empleado->save();

                                //Registrar relación entre user y customer (tabla customer_user) si no existe
                                $location = Location::find($params_array['location_id']);
                                $cliente_id = $location->customer_id;

                                $relacion = $user->customers()->find($cliente_id);

                                if($relacion == null){
                                    $user->customers()->attach($cliente_id);
                                }

                                //obtengo el empleado y su ubicación desde la BD
                                $newEmpleado = Employe::where([
                                    'location_id' => $params_array['location_id'],
                                    'user_id'     => $params_array['user_id']
                                ])->first();

                                //Registro expiration_control
                                $expiration_control = new Expiration_Control;
                                $expiration_control->employe_id = $newEmpleado->id;
                                $expiration_control->date = now();
                                $expiration_control->employe_status_id = 1;

                                $expiration_control->save();
                                    
                                $data = array( 
                                    'status'  => true,
                                    'message' => 'employeeRegisterOk',
                                    'response' => array('success' => 'El empleado se ha registrado correctamente.',
                                                        'newEmployee_Id' => $newEmpleado->id
                                                    )
                                );  

                            }
                        }else{

                            $data = array( 
                                'status'  => false,
                                'message' => 'employeeRegisterError',
                                'response' => array('error' => 'Un usuario de tipo Sistema no puede ser empleado.')
                            );
                        }
                        
                    }




                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'employeeRegisterError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'employeeRegisterError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }


    //-------------------------------UPDATE---------------------------

    public function update(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'updateError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            //Recoger datos por post
            $json = $request->input('json', null);
            $params_array = json_decode($json, true);
            
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema'){

                    //Validar los datos 
                    $validate = \Validator::make($params_array, [
                        
                            'position_type_id'  => 'required|integer',
                            'call_order'        => 'required|integer',
                            'caller_password'   => 'required', 
                            'personal_password' => 'required',
                            'panel_order'       => 'required|integer',
                            'panel_code'        => 'required|integer', 
                            'biometric_id'      => 'required|integer'
                        
                    ]);

                    //Checkbox para renovar validez códigos
                    $expiration_renew = $params_array['expiration_renew'];
                    
                    //Empleado a actualizar
                    $emp_id = $params_array['employee_id'];

                    //Quitar los campos que no quiero actualizar
                        unset($params_array['expiration_renew']);
                        unset($params_array['employee_id']);
                        unset($params_array['location_id']);
                        unset($params_array['user_id']);
                        unset($params_array['created_at']);
                        unset($params_array['updated_at']);
                        unset($params_array['cancel_at']);
                        unset($params_array['cancel_request']);

                        if($validate->fails()){

                            //Convierto el mensaje de error de Laravel a nuestro formato

                            $a = json_encode($validate->errors());
                            $b = json_decode($a);

                            foreach($b as $clave => $valor){
                                    $error = $b->$clave[0];
                            }

                            $data = array(
                                'status'    => false,
                                'message' => 'updateError',
                                'response' => array('error' => str_replace('web ', '',$error))
                            );

                        }else{

                            
                            
                            //Verificar si se debe actualizar expiration_controls
                            $empleado = Employe::where([
                                'id' => $emp_id
                            ])->first();
                            
                            $expiration_renew2 = false;

                            if($params_array['caller_password'] != Security::decrypt($empleado->caller_password) &&
                               $params_array['personal_password'] != Security::decrypt($empleado->personal_password) &&
                               $params_array['panel_code'] != Security::decrypt($empleado->panel_code)){
                                $expiration_renew2 = true;
                            }

                            if($expiration_renew || $expiration_renew2){

                                $expiration_array = array('date'        => now(),
                                                  'employe_status_id'   => 1);
                                $expiration_update = Expiration_control::where('employe_id', $emp_id)->update($expiration_array);
                            }
                            
                            //Cifrado de passwords
                            $params_array['caller_password'] = Security::encrypt($params_array['caller_password']);
                            $params_array['personal_password'] = Security::encrypt($params_array['personal_password']);
                            $params_array['panel_code'] = Security::encrypt($params_array['panel_code']);

                            //Actualizar usuario
                            $employee_update = Employe::where('id', $emp_id )->update($params_array);

                            //Devolver array resultado
                            $data = array( 
                                'status'  => true,
                                'message' => 'updateOk',
                                'response' => array('success' => 'Los datos se han actualizado correctamente')
                            );
                        }   


                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'updateError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'updateError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }


    //-------------------------------getEmployeeById---------------------------
    
    public function getEmployeeById(Request $request, $employee_id){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'getEmployeeByIdError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                $empleado = Employe::where(['id' => $employee_id])->first();

                if(is_object($empleado)){ 

                    if($user_type == 'Empleado'){

                        $user_id = $decoded->sub;
                        
                            
                        if($empleado->user_id == $user_id){
                            $emp_array = array();
                                //Reviso si está dado de baja (cancel_at)
                                if($empleado->cancel_at == null){
                                    $emp['employee_id'] = $empleado->id;
                                    $emp['location_id'] = $empleado->location_id;
                                    $user['user_id'] = $empleado->user->id;
                                    $user['dni']     = $empleado->user->dni;
                                    $user['name']    = $empleado->user->name;
                                    $user['surname'] = $empleado->user->surname;
                                    $user['phone']   = $empleado->user->phone;
                                    $user['email']   = $empleado->user->email;
                                    $user['locked_account']  = $empleado->user->locked == 1?true : false;
                    
                                    $emp['position_type']       = $empleado->positionType->position_name;
                                    $emp['position_type_id']    = $empleado->position_type_id;
                                    $emp['call_order']          = $empleado->call_order;
                                    $emp['caller_password']     = Security::decrypt($empleado->caller_password);
                                    $emp['personal_password']   = Security::decrypt($empleado->personal_password);
                                    $emp['panel_order']         = $empleado->panel_order;
                                    $emp['panel_code']          = Security::decrypt($empleado->panel_code);
                                    $emp['biometric_id']        = $empleado->biometric_id;
                                    $emp['employee_status_id']  = $empleado->expirationControl->employe_status_id;
                                    $emp['user'] =  $user;
                                    array_push($emp_array, $emp);
        
                                    $data = array( 
                                        'status'  => true,
                                        'message' => 'getEmployeeByIdsOk',
                                        'response' => $emp_array
                                    );
                                }else{
                                    $data = array( 
                                        'status'  => false,
                                        'message' => 'getEmployeeByIdError',
                                        'response' => array('error' => 'Su cuenta ha sido dada de baja.')
                                    );
                                }
        
                            
                        }else{
                            $data = array( 
                                'status'  => false,
                                'message' => 'getEmployeeByIdError',
                                'response' => array('error' => 'Su perfil no tiene acceso a este empleado.')
                            );
                        }
                        
                    }else{

                            $emp_array = array();
                                        
                                //Reviso si está dado de baja (cancel_at)
                                if($empleado->cancel_at == null){
                                    $emp['employee_id'] = $empleado->id;
                                    $emp['location_id'] = $empleado->location_id;
                                    $user['user_id'] = $empleado->user->id;
                                    $user['dni']     = $empleado->user->dni;
                                    $user['name']    = $empleado->user->name;
                                    $user['surname'] = $empleado->user->surname;
                                    $user['phone']   = $empleado->user->phone;
                                    $user['email']   = $empleado->user->email;
                                    $user['locked_account']  = $empleado->user->locked == 1?true : false;
                    
                                    $emp['position_type']   = $empleado->positionType->position_name;
                                    $emp['position_type_id']   = $empleado->position_type_id;
                                    $emp['call_order']   = $empleado->call_order;
                                    //Info sólo disponible para sistema
                                    if($user_type == 'Sistema'){
                                        $emp['caller_password']   = Security::decrypt($empleado->caller_password);
        
                                        $emp['personal_password']   = Security::decrypt($empleado->personal_password);
                                    }
                                    $emp['panel_order']   = $empleado->panel_order;
                                    //Info sólo disponible para sistema
                                    if($user_type == 'Sistema'){
                                        $emp['panel_code']   = Security::decrypt($empleado->panel_code);
                                    }
                                    $emp['biometric_id']   = $empleado->biometric_id;
                                    $emp['employee_status_id']  = $empleado->expirationControl->employe_status_id;

                                    $emp['user'] =  $user;
                                    array_push($emp_array, $emp);

                                    $data = array( 
                                        'status'  => true,
                                        'message' => 'getEmployeeByIdOk',
                                        'response' => $emp_array
                                    );
                                }else{

                                    $data = array( 
                                        'status'  => false,
                                        'message' => 'getEmployeeByIdError',
                                        'response' => array('error' => 'Este empleado ha sido dada de baja.')
                                    );
                                }
                    
                    }
                }else{
                    $data = array( 
                        'status'  => false,
                        'message' => 'getEmployeeByIdError',
                        'response' => array('error' => 'El empleado no existe.')
                    );
                }

            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'getEmployeeByIdError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }


    //-------------------------------CancelRequest---------------------------

    
    public function cancelRequest(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'CancelRequestError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;

                $json = $request->input('json', null);
                $params_array = json_decode($json, true);
                $empleado_id = $params_array['employee_id'];

                $empleado = Employe::where(['id' => $empleado_id])->first();
                
                //Compruebo si ya está de baja
                if($empleado->cancel_at != null){
                    $data = array( 
                        'status'  => false,
                        'message' => 'cancelRequestError',
                        'response' => array('error' => 'El empleado ya se dio de baja anteriormente.')
                    );

                //Compruebo si es una solicitud duplicada
                }elseif($empleado->cancel_request != null){
                    $data = array( 
                        'status'  => false,
                        'message' => 'cancelRequestError',
                        'response' => array('error' => 'Ya se ha solicitado anteriormente la baja de este empleado.')
                    );

                }else{

                    //Actualizar usuario
                    $cancel_request = ['cancel_request' => now()];
                    $empleado_update = Employe::where('id', $empleado_id)->update($cancel_request);
                    
                    $data = array( 
                        'status'  => true,
                        'message' => 'cancelRequestOk',
                        'response' => array('success' => 'Se ha solicitado con éxito la baja del empleado con id ' . $empleado_id) 
                    );
                }

            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'CancelRequestError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }


    //-------------------------------getAllEmployeesCancelRequest---------------------------


    public function getAllEmployeesCancelRequest(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'getAllEmployeesCancelRequestError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema'){

                   //Obtengo empleados de los que se ha solicitado baja
                    $employees = Employe::whereNotNull(
                    'cancel_request'
                    )->get();

                    //Verifico si hay empleados para dar de baja
                    if(!$employees->isEmpty()){
                        $emp_array = array(); 

                        //Creo un array con las solicitudes
                        foreach($employees as $empleado){

                            $location_name = Location::where(['id' => $empleado->location_id])->first()->name;
                            //Ignora los empleaods que ya están dados de baja
                            if($empleado->cancel_at == null){
                                $emp['employee_id'] = $empleado->id;
                                $emp['location_id'] = $empleado->location_id;
                                $emp['location_name'] = $location_name;
                                $user['user_id'] = $empleado->user->id;
                                $user['dni']     = $empleado->user->dni;
                                $user['name']    = $empleado->user->name;
                                $user['surname'] = $empleado->user->surname;
                                $user['phone']   = $empleado->user->phone;
                                $user['email']   = $empleado->user->email;
                                $user['locked_account']  = $empleado->user->locked == 1?true : false;
                
                                $emp['position_type']   = $empleado->positionType->position_name;
                                $emp['position_type_id']   = $empleado->position_type_id;
                                $emp['call_order']   = $empleado->call_order;
                                $emp['caller_password']   = Security::decrypt($empleado->caller_password);
                                $emp['personal_password']   = Security::decrypt($empleado->personal_password);
                                $emp['panel_order']   = $empleado->panel_order;
                                $emp['panel_code']   = Security::decrypt($empleado->panel_code);
                                $emp['biometric_id']   = $empleado->biometric_id;
                                $emp['employee_status_id']  = $empleado->expirationControl->employe_status_id;
                                $emp['cancel_request_date']  = $empleado->cancel_request;

                                $emp['user'] =  $user;
                                array_push($emp_array, $emp);
                            }
                        }
        
                        $data = array( 
                            'status'  => true,
                            'message' => 'getAllEmployeesCancelRequestOk',
                            'response' => $emp_array
                        );
                    }else{
                        $data = array( 
                            'status'  => false,
                            'message' => 'getAllEmployeesCancelRequestError',
                            'response' => array('error' => 'No hay empleados pendientes de baja.')
                        );
                    }    

                    


                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'getAllEmployeesCancelRequestError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'getAllEmployeesCancelRequestError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }

    //-------------------------------cancel---------------------------

    public function cancel(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'cancelError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema'){

                    $json = $request->input('json', null);
                    $params_array = json_decode($json, true);
                    
                    //Obtengo el id del empleado a dar de baja
                    $employee_id = $params_array['employee_id'];
            
                    if(!empty($employee_id)){
                        
                        //Obtengo desde la BD todos sus datos
                        $employee = Employe::where([
                            'id' => $employee_id
                        ])->first();
    
                        //Compruebo si ya se había dado de baja    
                        if($employee->cancel_at != null){
                            $data = array( 
                                'status'  => false,
                                'message' => 'cancelError',
                                'response' => array('error' => 'El empleado fue dado de baja anteriormente.')
                            );
            
                        }else{
                            
                            //Baja
                            $cancel_at = ['cancel_at' => now()];
                            $employee_update = Employe::where('id', $employee_id)->update($cancel_at);
                            
                            $user = User::where(['id' => $employee->user_id])->first();


                            //Si todos los empleados de un usuario están de baja, también se da de baja el usuario
                            $user_employees = Employe::where(['user_id' => $employee->user_id])->get();
                            if($user_employees->count() === $user_employees->whereNotNull('cancel_at')->count()){
                                //Baja empleado
                                $cancel_at = ['cancel_at' => now()];
                                $user_update = User::where('id', $employee->user_id)->update($cancel_at);
                            }

                            $data = array( 
                                'status'  => true,
                                'message' => 'cancelOk',
                                'response' => array('success' => 'Se ha dado de baja al empleado con id: ' . $employee->id . 
                                                                 ' que corresponde al usuario ' . $user->name . ' '. $user->surname) 
                            );
                        }
    
    
    
                    }else{
                        $data = array( 
                            'status'  => false,
                            'message' => 'cancelError',
                            'response' => array('error' => 'Los datos no se han recibido correctamente.')
                        );
                    }




                    



                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'cancelError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'cancelError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }
}
