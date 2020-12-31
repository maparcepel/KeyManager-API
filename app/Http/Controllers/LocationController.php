<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Admin;
use App\Customer;
use App\Location;
use App\Employe;
use Illuminate\Http\Response;

class LocationController extends Controller
{
    public function getAllCustomersAndLocations(Request $request){

        //Recibe el token y lo envía pata decodificar
        $token = $request->header('authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array(
                'status'  => false,
                'message' => 'getAllCustomersAndLocationsError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );

        }else{
            $decoded = $jwtAuth->checkToken($token, true);
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                //extrae datos del token decodificado
                $email = $decoded->email;
                $user_type = $decoded->userType;


                if($user_type == 'Sistema'){
                    //Obtengo todos los clientes
                    $clientes = Customer::all();
                }else{
                    $user = User::where([
                        'email' => $email,
                    ])->first();

                    //Obtengo sólo los clientes con los que está relacionado
                    $clientes = $user->customers;
                }

                $data = array();
                $array_aux = array();

                //Obtengo id de las locations en caso de ser empleado
                if($user_type == 'Empleado'){
                    $location_ids = Employe::where(
                        'user_id' , $user->id
                    )->pluck('location_id')->toArray();
                }

                foreach($clientes as $cliente){
                    $array_cliente = array();
                    $array_cliente['name'] = $cliente->name;
                    $array_cliente['customer_id'] = $cliente->id;

                    //obtiene instalaciones asociadas


                    /* $locations = Location::find($emp);
                    var_dump($locations);
                    die(); */

                    $locations = $cliente->locations;

                    $array_locationS = array();

                    foreach($locations as $location){
                        if($user_type == 'Empleado' ){
                            if(in_array($location->id, $location_ids)){
                                $array_location = array();
                                $array_location['name'] =  $location->name;
                                $array_location['location_id'] = $location->id;
                                array_push($array_locationS, $array_location);
                            }

                        }else{
                            $array_location = array();
                            $array_location['name'] =  $location->name;
                            $array_location['location_id'] = $location->id;
                            array_push($array_locationS, $array_location);
                        }

                    }
                    $array_cliente['locations'] = $array_locationS;
                    array_push($array_aux, $array_cliente);

                }

                $data = array(
                    'status'  => true,
                    'message' => 'getAllCustomersAndLocationsOk',
                    'response' => $array_aux
                );

            }else{
                $data = array(
                    'status'  => false,
                    'message' => 'getAllCustomersAndLocationsError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }
        }

        return json_encode($data);

    }

    //-------------------------------getLocationById---------------------------

    public function getLocationById(Request $request, $location_id){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array(
                'status'  => false,
                'message' => 'getLocationByIdError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );

        }else{
            $decoded = $jwtAuth->checkToken($token, true);
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;

                    $location = Location::where(['id' => $location_id])->first();
                    $location['customer_name'] = $location->customer()->first()->name;

                    $zip_code = $location->zipCode()->first();
                    $city = $zip_code->city()->first();
                    $province = $zip_code->province()->first();
                    $country = $zip_code->country()->first();

                    $postal_data = array();
                    $postal_data['zip_code']        = $zip_code->zipcode;
                    $postal_data['city_name']       = $city->name;
                    $postal_data['city_id']         = $city->id;
                    $postal_data['province_name']   = $province->name;
                    $postal_data['province_id']     = $province->id;
                    $postal_data['country_name']    = $country->name;
                    $postal_data['country_id']      = $country->id;

                    $location['postal_data']  = $postal_data;

                    $data = array(
                        'status'  => true,
                        'message' => 'getLocationByIdOk',
                        'response' => $location
                    );

            }else{
                $data = array(
                    'status'  => false,
                    'message' => 'getLocationByIdError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        }

        return json_encode($data);
    }

    //-------------------------------Update---------------------------

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
            $decoded = $jwtAuth->checkToken($token, true);
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema'){

                    $json = $request->input('json', null);
                    $params_array = json_decode($json, true);

                    $location_id = $params_array['location_id'];

                    //Validar los datos
                    $validate = \Validator::make($params_array, [

                        'location_number'   => 'required',
                        'name'              => 'required',
                        'address'           => 'required',
                        'zip_code_id'       => 'required',
                        'customer_id'       => 'required',
                        'pass_expiration_days' => 'required'
                    ]);

                    //Location a actualizar
                    $location_id = $params_array['location_id'];

                    //Quitar los campos que no quiero actualizar
                    unset($params_array['location_id']);

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
                            'response' => array('error' => $error)
                        );

                    }else{

                    $location_update = Location::where('id', $location_id)->update($params_array);



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

    //-------------------------------register---------------------------

    public function register(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array(
                'status'  => false,
                'message' => 'registerError',
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
                        'location_number'   => 'required|unique:locations',
                        'name'              => 'required',
                        'address'           => 'required',
                        'zip_code_id'       => 'required',
                        'customer_id'       => 'required',
                        'pass_expiration_days' => 'required'

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
                            'message' => 'registerError',
                            'response' => array('error' => $error)
                        );

                    }else{

                            //creo la location
                            $location = new Location();
                            $location->location_number      = $params_array['location_number'];
                            $location->name                 = $params_array['name'];
                            $location->address              = $params_array['address'];
                            $location->zip_code_id          = $params_array['zip_code_id'];
                            $location->customer_id          = $params_array['customer_id'];
                            $location->pass_expiration_days = $params_array['pass_expiration_days'];
                            $location->comments             = $params_array['comments'];

                            $location->save();

                            $data = array(
                                'status'  => true,
                                'message' => 'updateOk',
                                'response' => array('success' => 'La localización se ha registrado correctamente.'));

                    }

                }else{

                    $data = array(
                        'status'  => false,
                        'message' => 'registerError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array(
                    'status'  => false,
                    'message' => 'registerError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        }

        return json_encode($data);
    }

    //-------------------------------cancelRequest---------------------------

    public function cancelRequest(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'cancelRequestError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $json = $request->input('json', null);
                $params_array = json_decode($json, true);
                $location_id = $params_array['location_id'];

                $location = Location::find($location_id);
                
                //Compruebo si ya está de baja
                if($location->cancel_at != null){
                    $data = array( 
                        'status'  => false,
                        'message' => 'cancelRequestError',
                        'response' => array('error' => 'Esta localización ya se dio de baja anteriormente.')
                    );

                //Compruebo si es una solicitud duplicada
                }elseif($location->cancel_request != null){
                    $data = array( 
                        'status'  => false,
                        'message' => 'cancelRequestError',
                        'response' => array('error' => 'Ya se ha solicitado anteriormente la baja de esta localización.')
                    );

                }else{

                    //Actualizar usuario
                    $cancel_request = ['cancel_request' => now()];
                    $location_update = Location::where('id', $location_id)->update($cancel_request);
                    
                    $data = array( 
                        'status'  => true,
                        'message' => 'cancelRequestOk',
                        'response' => array('success' => 'Se ha solicitado con éxito la baja de la localización ' . $location->name) 
                    );
                }

            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'cancelRequestError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }

    //-------------------------------getAllLocationsCancelRequest---------------------------
    public function getAllLocationsCancelRequest(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'getAllLocationsCancelRequestError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema'){

                                
                    //Obtengo locations de las que se ha solicitado baja
                    $locations = Location::whereNotNull(
                    'cancel_request'
                    )->get();

                    //Compruebo si hay locations para dar de baja
                    if(!$locations->isEmpty()){
                        $locations_array = array(); 

                        //Creo un array con las solicitudes
                        foreach($locations as $location){

                            //Ignora los empleaods que ya están dados de baja
                            if($location->cancel_at == null){

                                $location['customer_name'] =  $location->customer()->first()->name;
                                array_push($locations_array, $location);
                            }
                        }

                        $data = array( 
                            'status'  => true,
                            'message' => 'getAllLocationsCancelRequestOk',
                            'response' => $locations_array
                        );
                    }else{
                        $data = array( 
                            'status'  => false,
                            'message' => 'getAllLocationsCancelRequestError',
                            'response' => array('error' => 'No hay localizaciones pendientes de baja.')
                        );
                    }    


                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'getAllLocationsCancelRequestError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'getAllLocationsCancelRequestError',
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
                    
                    //Obtengo el id de la localización a dar de baja
                    $location_id = $params_array['location_id'];
            
                    if(!empty($location_id)){
                        
                        //Obtengo desde la BD todos sus datos
                        $location = Location::where([
                            'id' => $location_id
                        ])->first();
    
                        //Compruebo si ya se había dado de baja    
                        if($location->cancel_at != null){
                            $data = array( 
                                'status'  => false,
                                'message' => 'cancelError',
                                'response' => array('error' => 'La localización fue dado de baja anteriormente.')
                            );
            
                        }else{
                            
                            //Baja de la localización
                            $cancel_at = ['cancel_at' => now()];
                            $location_update = Location::where('id', $location_id)->update($cancel_at);
                            
                            //Baja de sus empleados
                            $num_employees = 0;
                            $employees = Employe::where(['location_id' => $location_id, 'cancel_at' => null])->get();
                            foreach($employees as $employee){
                                $num_employees ++;
                                $employee->update($cancel_at);

                                //Actualizo el estado el empleado en expiration_controls
                                $expiration_controls = $employee->expirationControl()->first();
                                $expiration_controls_data = ['date' => now(), 'employe_status_id' => 5];
                                $expiration_controls_update = $expiration_controls->update($expiration_controls_data);
                            }

                            

                            $data = array( 
                                'status'  => true,
                                'message' => 'cancelOk',
                                'response' => array('success' => 'Se ha dado de baja la localización: ' . $location->name . ' y a sus ' . $num_employees . ' empleados.') 
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
