<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request; 
use Illuminate\Support\Str;
use App\Model\Note; 
use App\Model\Note_insert; 
use Theme; 
use Auth; 
use AppHelper; 
use Route; 
use Validator; 
use Storage; 
use Carbon\Carbon;
use DB; 
use Cache; 
class ChannelController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function me(){
		if(Auth::check()){
			$user=Auth::user(); 
			if($this->_parame['type']=='trash'){
				$getNote=Note::where('type','channel')
					->where('status','delete')
					->where('user_id',(string)$user->id)
					->orderBy('updated_at','desc')
					->simplePaginate(10); 
				$data=array(
					'user'=>$user, 
					'getNote'=>$getNote
				); 
				return Theme::view('channel.trash', $data);
			}else if($this->_parame['type']=='active'){
				$getNote=Note::where('type','channel')
					->where('status','!=','delete')
					->where('user_id',(string)$user->id)
					->orderBy('updated_at','desc')
					->simplePaginate(10); 
				$data=array(
					'user'=>$user, 
					'getNote'=>$getNote
				); 
				return Theme::view('channel.active', $data);
			}
		}else{
			$data=array(); 
			return Theme::view('user.login', $data);
		}
	}
	public function channelList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('channelList_page_new_'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('_id','type','logo','title','description','created_at','updated_at')
			->where('type','channel')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('channel.list', $data);
	}
	public function channelAdd(Request $request){
		if(Auth::check()){
			$data=array(
				'user'=>Auth::user()
			); 
			return Theme::view('channel.add', $data);
		}else{
			$data=array(); 
			return Theme::view('user.login', $data);
		}
	}
	public function channelAddRequest(Request $request){
		if(Auth::check()){
			$user=Auth::user(); 
			$noteId=Input::get('channelId'); 
			$channelTitle=Input::get('channelTitle'); 
			$channelContent=Input::get('channelContent'); 
			$tags=Input::get('tags'); 
			$channelId=Input::get('channelId'); 
			$region=Input::get('idRegion'); 
			$regionName=Input::get('regionName'); 
			$subRegion=Input::get('idSubRegion'); 
			$subRegionName=Input::get('subRegionName'); 
			$address=Input::get('address'); 
			$name=Input::get('name'); 
			$email=Input::get('email'); 
			$phone=Input::get('phone'); 
			$hiddenEmail=Input::get('hiddenEmail'); 
			$hiddenPhone=Input::get('hiddenPhone'); 
			$category=explode(',',$tags); 
			$messages = array(
				'required' => 'Vui lòng nhập thông tin (*).',
			);
			$rules = array(
				'channelTitle' => 'required|string|max:255', 
				'channelContent'=>'required', 
				'idRegion'=>'required', 
				'idSubRegion'=>'required', 
				'address'=>'required', 
				'name'=>'required', 
				'email'=>'required|email', 
				'phone'=>'required'
			);
			$validator = Validator::make(Input::all(), $rules, $messages);
			if ($validator->fails())
			{
				$error=$validator->errors()->first(); 
			}
			if(empty($error)){
				if(AppHelper::instance()->checkBlacklistWord($channelTitle)){
					if(!empty($noteId)){
						$note=Note_insert::find($noteId); 
					}else{
						$checkIp=Note::where('type','ip')->where('title_encode',base64_encode($request->ip()))->first(); 
						if(!empty($checkIp->id)){
							if(!empty($checkIp->history['last_channel_add'])){
								if(Carbon::parse($checkIp->history['last_channel_add'])->addMinutes(10) > Carbon::now()->format('Y-m-d H:i:s')){
									return response()->json(['success'=>false,
										'message'=>'Mỗi bài đăng phải cách nhau ít nhất 10 phút.'
									]);
								}
							}else{
								if(!is_array($checkIp->history)){
									$checkIp->history=array(); 
								}
								$mer=array('last_channel_add'=>(string)Carbon::now()->format('Y-m-d H:i:s')); 
								$checkIp->history= array_merge($checkIp->history, $mer); 
								$checkIp->save(); 
							}
						}else{
							$data=array(
								'type'=>'ip', 
								'status'=>'pending'
							); 
							$noteIp=new Note_insert($data); 
							$noteIp->title=$request->ip(); 
							$noteIp->title_encode=base64_encode($request->ip()); 
							$noteIp->history=array(
								'last_channel_add'=>(string)Carbon::now()->format('Y-m-d H:i:s')
							); 
							$noteIp->save(); 
						}
						$data=array(
							'type'=>'channel', 
							'status'=>'pending'
						); 
						$note=new Note_insert($data); 
						$note->save(); 
					}
					if(!empty($note->id) && $note->type=='channel'){
						$note->title=htmlspecialchars(strip_tags($channelTitle, '')); 
						$note->title_encode=base64_encode(htmlspecialchars(strip_tags($channelTitle, ''))); 
						$note->user_id=(string)$user->id; 
						$note->description=htmlspecialchars($channelContent); 
						$note->tags=$tags; 
						if(!is_array($note->logo)){
							$note->logo=array(); 
						}
						if(!is_array($note->banner)){
							$note->banner=array(); 
						} 
						if(!empty($category) && count($category)>0){
							foreach($category as $value){
								if(!empty($value)){
									if(AppHelper::instance()->checkBlacklistWord($value)){
										$checkExist=Note_insert::where('title_encode',base64_encode($value))->where('type','category')->first(); 
										if(empty($checkExist->id)){
											$data=array(
												'type'=>'category', 
												'status'=>'pending'
											); 
											$noteCategory=new Note_insert($data); 
											$noteCategory->sub_type='tag';
											$noteCategory->title=$value; 
											$noteCategory->title_encode=base64_encode($value); 
											$noteCategory->scores=0; 
											$noteCategory->attribute=array(); 
											$noteCategory->get_result='none'; 
											$noteCategory->replay=0; 
											$noteCategory->save(); 
										}
									}
								}
							}
						}
						if(!is_array($note->address)){
							$note->address=array(); 
						}
						$merAddress=array(
							'address'=>$address,
							'region'=>array(
								'region_id'=>$region, 
								'region_name'=>$regionName
							), 
							'subregion'=>array(
								'subregion_id'=>$subRegion, 
								'subregion_name'=>$subRegionName
							)
						); 
						$note->address= array_merge($note->address, $merAddress); 
						if(!empty($name)){
							$note->user_name=$name; 
						}
						if(!is_array($note->email)){
							$note->email=array(); 
						}
						$merEmail=array('email'=>$email); 
						$note->email= array_merge($note->email, $merEmail); 
						if(!is_array($note->phone)){
							$note->phone=array(); 
						}
						$merPhone=array('phone'=>$phone); 
						$note->phone= array_merge($note->phone, $merPhone); 
						if(!empty($hiddenEmail)){
							$note->hiddenEmail=$hiddenEmail; 
						}
						if(!empty($hiddenPhone)){
							$note->hiddenPhone=$hiddenPhone; 
						}
						$note->view=0; 
						$note->status='active'; 
						$note->save(); 
						$user->save(); 
						return response()->json(['success'=>true,
							'message'=>'Kênh của bạn đã được tạo thành công! ', 
							'noteId'=>(string)$note->id, 
							'link'=>route('profile',array(config('app.url'),$note->id)), 
						]);
					}
				}
			}
		}
	}
}