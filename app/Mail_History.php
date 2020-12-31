<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Mail_History extends Model
{
    protected $table = 'mail_histories';

    //Relación de muchos a uno
    public function employee(){
        return $this->belongsTo('App\Employe', 'employe_id');
    }
}
