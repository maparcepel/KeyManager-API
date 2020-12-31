<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position_Type extends Model
{
    protected $table = 'position_types';

    public function employes(){
        return $this->hasMany('App\Employe', 'position_type_id');
    }
}
