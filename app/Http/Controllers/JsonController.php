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
class JsonController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function index(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		if($this->_parame['type']=='region'){
			if($this->_parame['id']=='all'){
				$regionList = Cache::store('memcached')->remember('regionList',1, function()
				{
					return DB::connection('mongodb')->collection('note')
					->select('_id','title','iso','description')
					->where('type','category')
					->where('sub_type','region')
					//->where('status','active')
					->orderBy('updated_at','desc')
					->get(); 
				}); 
				return response()->json(['success'=>true,
					'message'=>'Danh sách quốc gia',
					'region'=>$regionList
				]);
			}else{
				$region=Note::find($this->_parame['id']); 
				return response()->json(['success'=>true,
					'message'=>'Quốc gia',
					'region'=>$region
				]);
			}
		}else if($this->_parame['type']=='subregion'){
			$regonId=$this->_parame['id']; 
			$subregionList = DB::connection('mongodb')->collection('note')
				->select('_id','title','description')
				->where('type','category')
				->where('sub_type','sub_region')
				->where('parent',$regonId)
				->orderBy('updated_at','desc')
				->get();
			return response()->json(['success'=>true,
				'message'=>'Danh sách thành phố',
				'subregion'=>$subregionList
			]);
		}
	}
}