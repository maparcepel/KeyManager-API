<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';

    public function users(){
        return $this->belongsToMany('App\User');//actualizar
    }

    public function locations(){
        return $this->hasMany('App\Location');//actualizar
    }

    public function zipCode(){
        return $this->belongsTo('App\ZipCode');
    }
}
