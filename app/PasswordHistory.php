<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{
    protected $table = 'password_histories';
    protected $fillable = [
        'status'
    ];

    public function user(){
        return $this->belongsTo('App\User');
    }
}
