<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\PassRecovery;
use Mail;
use App\User;

class PassRecoveryController extends Controller
{
    public function getMail(Request $request){
        $json = $request->input('json', null);
        $params = json_decode($json);
        $params_array = json_decode($json, true);

        //Validar email
        $validate = \Validator::make($params_array, [
            'email' => 'required|email'
        ]);

        if($validate->fails()){
            //No pasa validación
            $data = array(
                'code'      => 400,
                'message'   => $validate->errors()
            );
        }else{
            //validar contra DB en helper
            $user = User::where([
                'email' => $params_array['email']
            ])->first();


            $email_data = [
                'name' => $user->name, 
                'surname' => $user->surname];    
            //$user = ['email' => $params_array['email']];
            Mail::to($params_array['email'])->send(new PassRecovery($email_data));

            $data = array(
                'code'      => 200,
                'message'   => 'email enviado correctamente.'
            );
        }



        return response()->json($data);
    }

    //----------------token---------------------------

    public function tokenMail(Request $request){

    }
}
