<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $table = 'countries';

    public function zipCodes(){
        return $this->hasMany('App\ZipCode');
    }
}
