<?php

namespace App\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;
//use Elasticquent\ElasticquentTrait; 
class Index_note extends Eloquent {
	//use ElasticquentTrait;
	protected $connection = 'mongodb';
    protected $collection = 'note';
	//public $timestamps = false; 
	protected $fillable = ['type', 'created_at','updated_at','status'];
}
