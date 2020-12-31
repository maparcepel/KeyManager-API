<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AccessHistory extends Model
{
    protected $table = 'access_histories';

    //Relación de muchos a uno
    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }
}
