<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Position_Type;

class PositionTypeController extends Controller
{
     //-------------------------------getAllPositionTypes---------------------------
     public function getAllPositionTypes(Request $request){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'getAllPositionTypesError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema'){

                    $position_types = Position_Type::all();
                    $array_aux = array();

                    foreach($position_types as $position_type){

                        $array_position_type = array();
                        $array_position_type['position_name'] = $position_type->position_name;
                        $array_position_type['position_id'] = $position_type->id;
                        array_push($array_aux, $array_position_type);
                    }

                    $data = array( 
                        'status'  => true,
                        'message' => 'getAllPositionTypesOk',
                        'response' => $array_aux
                    );


                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'getAllPositionTypesError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'getAllPositionTypesError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }
}
