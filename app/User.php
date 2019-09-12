<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
 use Illuminate\Auth\Authenticatable;
 use Illuminate\Auth\Passwords\CanResetPassword;
 use Illuminate\Foundation\Auth\Access\Authorizable;
 use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
 use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
class User extends Eloquent implements AuthenticatableContract, AuthorizableContract, CanResetPasswordContract
{
	protected $connection = 'mongodb';
	protected $collection = 'note'; 
	protected $fillable = ['type','name', 'email', 'password','created_at','updated_at']; 
	protected $hidden = ['password', 'remember_token']; 
    use Authenticatable, Authorizable, CanResetPassword; 
	public function channel(){
        return $this->hasMany('App\Model\Note', 'user_id', '_id')->where('type','channel');
    }
}