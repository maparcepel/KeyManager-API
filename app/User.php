<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'surname', 'DNI', 'email', 'web_password', 'user_type_id', 'phone', 'cancel_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'web_password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    //Realación de uno a muchos
    public function accessHistory(){
        return $this->hasMany('App\AccessHistory');
    }

    public function userType(){
        return $this->belongsTo('App\UserType', 'user_type_id');
    }

    public function customers(){
        return $this->belongsToMany('App\Customer');//actualizar
    }

    public function employe(){
        return $this->hasMany('App\Employe');
    }

    public function passwordResets(){
        return $this->hasMany('App\PasswordReset');
    }

    public function passwordHistories(){
        return $this->hasMany('App\PasswordHistory');
    }
}
