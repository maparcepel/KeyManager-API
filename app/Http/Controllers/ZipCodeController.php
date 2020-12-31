<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\City;
use App\Province;
use App\Country;
use App\ZipCode;

class ZipCodeController extends Controller
{
     //-------------------------------getDataByZipCode---------------------------

     public function getDataByZipCode(Request $request, $zip_code){

        $token = $request->header('Authorization');
        $jwtAuth = new \JwtAuth();
        $checkToken = $jwtAuth->checkToken($token);

        //Compruebo validez del token
        if(!$checkToken){
            $data = array( 
                'status'  => false,
                'message' => 'getDataByZipCodeError',
                'response' => array('error' => 'Su sesión ha caducado. Vuelva a iniciar sesión.')
            );
            
        }else{
            $decoded = $jwtAuth->checkToken($token, true);  
            if(!empty($decoded) && is_object($decoded) && isset($decoded->sub)){

                $user_type = $decoded->userType;
                if($user_type == 'Sistema' || $user_type == 'Cliente'){

                    $places = ZipCode::where(['zipcode' => $zip_code])->get();
         
                    if(!$places->isEmpty()){

                        $array_aux = array();

                        foreach($places as $place){
                            
                            $zipCode = array();
                            $zipCode['zip_code_id'] = $place->id;
                            $zipCode['zip_code']    = $zip_code;
                            $zipCode['country_id']  = $place->country()->first()->id;
                            $zipCode['country_name']= $place->country()->first()->name;
                            $zipCode['province_id'] = $place->province()->first()->id;
                            $zipCode['province_name']    = $place->province()->first()->name;
                            $zipCode['city_id']     = $place->city()->first()->id;
                            $zipCode['city_name']   = $place->city()->first()->name;

                            array_push($array_aux, $zipCode);
                        }
                        
                        $data = array( 
                            'status'  => true,
                            'message' => 'getDataByZipCodesOk',
                            'response' => $array_aux
                        );
                    }else{

                        $data = array( 
                            'status'  => false,
                            'message' => 'getDataByZipCodeError',
                            'response' => array('error' => 'Este código postal no consta en nuestra base de datos.')
                        );

                    }

                    


                }else{
                    
                    $data = array( 
                        'status'  => false,
                        'message' => 'getDataByZipCodeError',
                        'response' => array('error' => 'No tiene privilegios para acceder a esta información.')
                    );
                }


            }else{
                $data = array( 
                    'status'  => false,
                    'message' => 'getDataByZipCodeError',
                    'response' => array('error' => 'Se ha producido un error. Inténtelo más tarde.')
                );
            }

        } 
        
        return json_encode($data);   
    }
}
