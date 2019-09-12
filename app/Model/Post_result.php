<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
class Post_result extends Model
{
	protected $connection = 'mysql';
    protected $table = 'post_result';
	public $timestamps = false; 
}