<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ZipCode extends Model
{
    protected $table = 'zip_codes';

    public function locations(){
        return $this->hasMany('App\Location');
    }

    public function city(){
        return $this->belongsTo('App\City');
    }

    public function province(){
        return $this->belongsTo('App\Province');
    }

    public function country(){
        return $this->belongsTo('App\Country');
    }

    public function customers(){
        return $this->hasMany('App\Customer');
    }
}
