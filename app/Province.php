<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Province extends Model
{
    protected $table = 'provinces';

    public function zipCodes(){
        return $this->hasMany('App\ZipCode');
    }
}
