<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request; 
use App\Model\Note; 
use App\User; 
use Theme; 
use Auth; 
use Cache; 
use DB; 
use URL; 
class DomainController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function setStatus(){
		if(!empty($this->_parame['slug'])){
			$note=Note::where('type','domain')->where('domain_encode',base64_encode($this->_parame['slug']))->first(); 
			if(!empty($note->id)){
				$action=Input::get('action'); 
				if($action=='disableads'){
					$noteMer=array('ads'=>'disable'); 
					$note->attribute= array_merge($note->attribute, $noteMer);
				}else if($action=='activeads'){
					$noteMer=array('ads'=>'active'); 
					$note->attribute= array_merge($note->attribute, $noteMer);
				}else if($action=='blacklist'){
					$note->status='blacklist'; 
				}else if($action=='active'){
					$note->status='active'; 
				}else if($action=='pending'){
					$note->status='pending'; 
				}else if($action=='delete'){
					$note->status='delete'; 
				}else if($action=='destroy'){
                    $note->delete();
                    return response()->json(['success'=>true,
                        'message'=>'Đã cập nhật trạng thái tên miền '.$note->domain,
                    ]);
                }
				$note->save(); 
				return response()->json(['success'=>true,
					'message'=>'Đã cập nhật trạng thái tên miền '.$note->domain, 
				]);
			}else{
				return response()->json(['success'=>true,
					'message'=>'Không tìm thấy tên miền ', 
				]);
			}
		}
	}
}