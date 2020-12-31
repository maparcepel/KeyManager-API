<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{

    protected $fillable = [
        'cancel_at'
    ];
    protected $table = 'locations';

    public function customer(){
        return $this->belongsTo('App\Customer'); //actualizar
    }

    public function employes(){
        return $this->hasMany('App\Employe');
    }

    public function zipCode(){
        return $this->belongsTo('App\ZipCode');
    }
}
