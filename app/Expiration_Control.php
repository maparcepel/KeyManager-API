<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Expiration_Control extends Model
{
    protected $fillable = [
        'date', 'employe_status_id'
    ];
    protected $table = 'expiration_controls';

    public function employeeStatus(){
        return $this->belongsTo('App\Employe_Status');
    }

    public function employee(){
        return $this->belongsTo('App\Employe');
    }
}
