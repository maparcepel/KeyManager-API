<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;

class PruebasController extends Controller
{
    public function testOrm(){
        $users = User::all();
        foreach($users as $user){
            echo "<h1>{ $user->name}</h1>" ;
        }
        die();
    }
}
