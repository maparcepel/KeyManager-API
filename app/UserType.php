<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class UserType extends Model
{
    //protected $table = 'UserTypes';

    public function users(){
        $this->hasMany('App\User');
    }
}
