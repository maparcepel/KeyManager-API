<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Customer;

class CustomerController extends Controller
{
    //-------------------------------getCustomerById---------------------------

    public function getCustomerById(Request $request, $customer_id){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            
            $data = array( 
                'status'  => false,
                'message' => 'getCustomerByIdError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;

                    $customer = Customer::where(['id' => $customer_id])->first();

                    if(is_object($customer)){ 
                    
                        $zip_code   = $customer->zipCode()->first();
                        $city       = $zip_code->city()->first();
                        $province   = $zip_code->province()->first();
                        $country    = $zip_code->country()->first();

                        $postal_data['zip_code']        = $zip_code->zipcode;
                        $postal_data['city_name']       = $city->name;
                        $postal_data['city_id']         = $city->id;
                        $postal_data['province_name']   = $province->name;
                        $postal_data['province_id']     = $province->id;
                        $postal_data['country_name']    = $country->name;
                        $postal_data['country_id']      = $country->id;

                        $customer['postal_data']  = $postal_data;       

                        $data = array( 
                            'status'  => true,
                            'message' => 'getCustomerByIdOk',
                            'response' => $customer
                        );
                    }else{

                        $data = array( 
                            'status'  => false,
                            'message' => 'getCustomerByIdError',
                            'response' => array('error' => 'No existe un cliente con esta id.')
                        );                        
                    }

                


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'getCustomerByIdError',
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

                $user_type = $decoded->userType;
                if($user_type == 'Sistema' || $user_type == 'Cliente'){

                    $json = $request->input('json', null);
                    $params_array = json_decode($json, true);
                    $customer_id = $params_array['customer_id'];

                    $customer = Customer::find($customer_id);
                    
                    //Compruebo si ya está de baja
                    if($customer->cancel_at != null){
                        $data = array( 
                            'status'  => false,
                            'message' => 'cancelRequestError',
                            'response' => array('error' => 'Este cliente ya se dio de baja anteriormente.')
                        );

                    //Compruebo si es una solicitud duplicada
                    }elseif($customer->cancel_request != null){
                        $data = array( 
                            'status'  => false,
                            'message' => 'cancelRequestError',
                            'response' => array('error' => 'Ya se ha solicitado anteriormente la baja de este cliente.')
                        );

                    }else{

                        //Actualizar usuario
                        $cancel_request = ['cancel_request' => now()];
                        $customer_update = Customer::where('id', $customer_id)->update($cancel_request);
                        
                        $data = array( 
                            'status'  => true,
                            'message' => 'cancelRequestOk',
                            'response' => array('success' => 'Se ha solicitado con éxito la baja del cliente ' . $customer->name) 
                        );
                    }


                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'cancelRequestError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
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

    //-------------------------------getAllCustomersCancelRequest---------------------------

    public function getAllCustomersCancelRequest(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'getAllCustomersCancelRequestError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema'){

                                
                    //Obtengo clientes de las que se ha solicitado baja
                    $customers = Customer::whereNotNull(
                    'cancel_request'
                    )->where(['cancel_at' => null])->get();

                    //Compruebo si hay customers para dar de baja
                    if(!$customers->isEmpty()){
                       
                        $data = array( 
                            'status'  => true,
                            'message' => 'getAllCustomersCancelRequestOk',
                            'response' => $customers
                        );

                    }else{
                        $data = array( 
                            'status'  => false,
                            'message' => 'getAllCustomersCancelRequestError',
                            'response' => array('error' => 'No hay clientes pendientes de baja.')
                        );
                    }    


                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'getAllCustomersCancelRequestError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'getAllCustomersCancelRequestError',
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
                    
                    //Obtengo el id del cliente a dar de baja
                    $customer_id = $params_array['customer_id'];
            
                    if(!empty($customer_id)){
                        
                        //Obtengo desde la BD todos sus datos
                        $customer = Customer::where([
                            'id' => $customer_id
                        ])->first();
    
                        //Compruebo si ya se había dado de baja    
                        if($customer->cancel_at != null){
                            $data = array( 
                                'status'  => false,
                                'message' => 'cancelError',
                                'response' => array('error' => 'El cliente fue dado de baja anteriormente.')
                            );
            
                        }else{
                            
                            //Baja del cliente
                            $cancel_at = ['cancel_at' => now()];
                            $customer_update = Customer::where('id', $customer_id)->update($cancel_at);
                            
                            //Baja de sus locations y empleados
                            $locations = $customer->locations()->get();

                            if(is_object($locations)){

                                $cancel_at = ['cancel_at' => now()];

                                foreach($locations as $location){
                                    $location->update($cancel_at);

                                    $employees = $location->employes()->get();

                                    if(is_object($employees)){
                                        
                                        foreach($employees as$employee){
                                            $employee->update($cancel_at);

                                            //Actualizo el estado del empleado en expiration_controls
                                            $expiration_controls = $employee->expirationControl()->first();
                                            $expiration_controls_data = ['date' => now(), 'employe_status_id' => 5];
                                            $expiration_controls_update = $expiration_controls->update($expiration_controls_data);
                                        }
                                    }
                                }
                            }

                            //Baja de sus usuarios
                            $users = $customer->users()->get();

                            if(is_object($users)){
                                foreach($users as $user){
                                    $user->update($cancel_at);
                                }
                            }

                            $data = array( 
                                'status'  => true,
                                'message' => 'cancelOk',
                                'response' => array('success' => 'Se ha dado de baja el cliente ' . $customer->name . ' y a todas sus localizaciones, empleados y usuarios.') 
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

    //-------------------------------REGISTER---------------------------

    public function register(Request $request){

        $token = $request->header('authorization');
        $jwtAuth = new \JwtAuth();
        $decoded = $jwtAuth->checkToken($token, true);
        $checkToken = $jwtAuth->checkToken($token);

        //Recoger datos por post
        $json = $request->input('json', null);
        $params = json_decode($json);   //objeto
        $params_array = json_decode($json, true);  // array para validar

        //Compruebo validez del token
        if(!$checkToken){
            $data = array(
                'status'  => false,
                'message' => 'registerError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );

        }elseif(!empty($decoded) && is_object($decoded) && !empty($params) && !empty($params_array)){

            if($decoded->userType == 'Sistema'){

                $params_array = array_map('trim', $params_array);

                //Validar datos
                    $validate = \Validator::make($params_array, [

                        'customer_number'   => 'required|unique:customers',
                        'cif'               => 'required|unique:customers',
                        'name'              => 'required',
                        'address'           => 'required',
                        'zip_code_id'       => 'required'
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
                        'response' => array('error' => str_replace('web ', '',$error))
                    );

                }else{
                    //Validación pasada correctamente
                    
                        //crear el usuario
                        $customer = new Customer();
                        $customer->customer_number  = $params_array['customer_number'];
                        $customer->cif              = $params_array['cif'];
                        $customer->name             = $params_array['name'];
                        $customer->address          = $params_array['address'];
                        $customer->zip_code_id      = $params_array['zip_code_id'];
                        $customer->comments         = $params_array['comments'];

                        $customer->save();

                        $newCustomer = Customer::where([
                            'customer_number' => $params_array['customer_number'],
                        ])->first();

                        $data = array(
                            'status'  => true,
                            'message' => 'registerOk',
                            'response' => array('success' => 'Se ha registrado correctamente el cliente ' . $newCustomer->name
                                            )
                        );
                    
                }
            }else{
                $data = array(
                    'status'  => false,
                    'message' => 'registerError',
                    'response' => array('error' => 'No tiene privilegios para dar de alta usuarios.')
                );
            }
        }else{

            $data = array(
                'status'  => false,
                'message' => 'registerError',
                'response' => array('error' => 'Los datos no se han enviado correctamente.')
            );

        }

        return response()->json($data);
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

                    $params_array = array_map('trim', $params_array);
                    //Id cliente a actualizar
                    $customer_id = $params_array['customer_id'];

                    //Validar datos
                        $validate = \Validator::make($params_array, [
    
                            'customer_number'   => 'required|unique:customers,customer_number,'.$customer_id,
                            'cif'               => 'required|unique:customers,cif,'.$customer_id,
                            'name'              => 'required',
                            'address'           => 'required',
                            'zip_code_id'       => 'required'
                        ]);

                    

                    //Quitar los campos que no quiero actualizar
                    unset($params_array['customer_id']);

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

                        $customer_update = Customer::where('id', $customer_id)->update($params_array);

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
}
