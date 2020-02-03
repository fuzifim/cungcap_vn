<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
class PageController extends ConstructController
{
    public function __construct(){
        parent::__construct();
    }
    public function goToUrl($url)
    {
        return view('goTo',['url'=>$url]);
    }
}
