<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employe_Status extends Model
{
    protected $table = 'employe_status';

    public function expiration_controls(){
        return $this->hasMany('App\Expiration_Control');
    }
}
