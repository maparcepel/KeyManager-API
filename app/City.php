<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $table = 'cities';

    public function zipCodes(){
        return $this->hasMany('App\ZipCode');
    }
}
