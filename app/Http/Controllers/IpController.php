<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request; 
use Theme; 
use Auth; 
use Cache; 
use DB; 
use App\Model\Note; 
class IpController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function show(){
		if(!empty($this->_parame['ip'])){
			$note=Note::where('type','ip')->where('title_encode',base64_encode($this->_parame['ip']))->first(); 
			if(!empty($note->id)){
				$newIp = Cache::store('memcached')->remember('newIp',1, function()
				{
					return DB::connection('mongodb')->collection('note')
					->select('type','title','description','created_at','updated_at')
					->where('type','ip')
					->where('status','active')
					->orderBy('updated_at','desc')
					->limit(20)->get(); 
				}); 
				$data=array(
					'note'=>$note, 
					'newIp'=>$newIp
				); 
				return Theme::view('ip.show', $data);
			}else{
				$keyword=str_replace('+', ' ', $this->_parame['ip']); 
				$keyword=str_replace('-', ' ', $keyword); 
				$data=array(
					'keyword'=>$keyword
				); 
				return Theme::view('404', $data);
			}
		}
	}
	public function ipList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('ipList'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('type','title','description','created_at','updated_at')
			->where('type','ip')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('ip.list', $data);
	}
}