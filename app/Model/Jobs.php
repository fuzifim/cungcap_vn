<?php

namespace App\Model;

use Jenssegers\Mongodb\Eloquent\Model as Eloquent;

class Jobs extends Eloquent {
	protected $connection = 'mongodb';
    protected $collection = 'jobs';

}
