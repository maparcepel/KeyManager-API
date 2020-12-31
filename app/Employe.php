<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employe extends Model
{
    protected $fillable = [
        'cancel_at'
    ];
    protected $table = 'employes';

    public function location(){
        return $this->hasMany('App\Location');
    }

    public function user(){
        return $this->belongsTo('App\User', 'user_id');
    }

    public function positionType(){
        return $this->belongsTo('App\Position_Type');
    }

    public function expirationControl(){
        return $this->hasOne('App\Expiration_Control');
    }
}
