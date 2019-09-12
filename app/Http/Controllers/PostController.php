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
use App\Http\Controllers\MediaController; 
class PostController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function show(){
		$data=array(); 
		return Theme::view('post.show', $data);
	}
	public function me(){
		if(Auth::check()){
			$user=Auth::user(); 
			if($this->_parame['type']=='trash'){
				$getNote=Note::where('type','post')
					->where('status','!=','active')
					->where('user_id',(string)$user->id)
					->orderBy('updated_at','desc')
					->simplePaginate(10); 
				$data=array(
					'user'=>$user, 
					'getNote'=>$getNote
				); 
				return Theme::view('post.trash', $data);
			}else if($this->_parame['type']=='active'){
				$getNote=Note::where('type','post')
					->where('status','active')
					->where('user_id',(string)$user->id)
					->orderBy('updated_at','desc')
					->simplePaginate(10); 
				$data=array(
					'user'=>$user, 
					'getNote'=>$getNote
				); 
				return Theme::view('post.active', $data);
			}
		}else{
			$data=array(); 
			return Theme::view('user.login', $data);
		}
	}
	public function add(){
		if(Auth::check()){
			$data=array(
				'user'=>Auth::user()
			); 
			return Theme::view('post.add', $data);
		}else{
			$data=array(); 
			return Theme::view('user.login', $data);
		}
	}
	public function edit(){
		if(Auth::check()){
			$user=Auth::user(); 
			if(!empty($this->_parame['id'])){
				$note=Note::where('type','post')->where('_id',$this->_parame['id'])->where('user_id',(string)$user->id)->first(); 
				if(!empty($note->id)){
					$data=array(
						'user'=>$user, 
						'note'=>$note
					); 
					return Theme::view('post.add', $data);
				}else{
					abort(404);
				}
			}else{
				abort(404);
			}
		}else{
			$data=array(); 
			return Theme::view('user.login', $data);
		}
	}
	public function deleteType(){
		if(Auth::check()){
			$user=Auth::user(); 
			$postId=Input::get('postId');
			if(!empty($postId)){
				$note=Note::where('type','post')->where('_id',$postId)->where('user_id',(string)$user->id)->first(); 
				if(!empty($note->id)){
					if($this->_parame['type']=='trash'){
						$note->status='delete'; 
						$note->save(); 
						return response()->json(['success'=>true,
							'message'=>'Bài đăng đã được đưa vào thùng rác!  '
						]);
					}else if($this->_parame['type']=='reactive'){
						$note->status='active'; 
						$note->save(); 
						return response()->json(['success'=>true,
							'message'=>'Bài đăng của bạn đã được khôi phục!  '
						]);
					}else if($this->_parame['type']=='forever'){
						if(!empty($note->media) && count($note->media)){
							$s3=Storage::disk('s3'); 
							foreach($note->media as $media){
								if($s3->exists(str_replace("//img.cungcap.net/","",$media['url']))) {
									$s3->delete(str_replace("//img.cungcap.net/","",$media['url']));
								}
								if($s3->exists(str_replace("//img.cungcap.net/","",$media['url_thumb']))) {
									$s3->delete(str_replace("//img.cungcap.net/","",$media['url_thumb']));
								}
								if($s3->exists(str_replace("//img.cungcap.net/","",$media['url_small']))) {
									$s3->delete(str_replace("//img.cungcap.net/","",$media['url_small']));
								}
								if($s3->exists(str_replace("//img.cungcap.net/","",$media['url_xs']))) {
									$s3->delete(str_replace("//img.cungcap.net/","",$media['url_xs']));
								}
							}
						}
						$note->delete();  
						return response()->json(['success'=>true,
							'message'=>'Đã xóa bài viết vĩnh viễn! ', 
						]);
					}
				}else{
					return response()->json(['success'=>true,
						'message'=>'Không tìm thấy bài viết!  '
					]);
				}
			}else{
				return response()->json(['success'=>true,
						'message'=>'Không tìm thấy bài viết!  '
					]);
			}
		}else{
			return response()->json(['success'=>true,
				'message'=>'Bạn cần phải đăng nhập để sử dụng tính năng!  '
			]);
		}
	}
	public function addRequest(Request $request){
		if(Auth::check()){
			$user=Auth::user(); 
			$noteId=Input::get('postId'); 
			$postTitle=Input::get('postTitle'); 
			$postContent=Input::get('postContent'); 
			$price=Input::get('price'); 
			$medias=Input::get('medias'); 
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
			$mediaJsonDecode=json_decode($medias); 
			$category=explode(',',$tags); 
			$messages = array(
				'required' => 'Vui lòng nhập thông tin (*).',
			);
			$rules = array(
				'postTitle' => 'required|string|max:255', 
				'postContent'=>'required', 
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
				if(AppHelper::instance()->checkBlacklistWord($postTitle)){
					if(!empty($noteId)){
						$note=Note_insert::find($noteId); 
					}else{
						$checkIp=Note::where('type','ip')->where('title_encode',base64_encode($request->ip()))->first(); 
						if(!empty($checkIp->id)){
							if(!empty($checkIp->history['last_post_add'])){
								if(Carbon::parse($checkIp->history['last_post_add'])->addMinutes(2) > Carbon::now()->format('Y-m-d H:i:s')){
									return response()->json(['success'=>false,
										'message'=>'Mỗi bài đăng phải cách nhau ít nhất 2 phút.'
									]);
								}
							}else{
								if(!is_array($checkIp->history)){
									$checkIp->history=array(); 
								}
								$mer=array('last_post_add'=>(string)Carbon::now()->format('Y-m-d H:i:s')); 
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
								'last_post_add'=>(string)Carbon::now()->format('Y-m-d H:i:s')
							); 
							$noteIp->save(); 
						}
						if(count($mediaJsonDecode)<=0){
							$error='Bài đăng cần phải có ít nhất 1 hình ảnh, vui lòng thêm hình ảnh trước khi đăng! ';
						}
						$data=array(
							'type'=>'post', 
							'status'=>'pending'
						); 
						$note=new Note_insert($data); 
						$note->save(); 
					}
					if(!empty($note->id) && $note->type=='post'){
						$note->title=htmlspecialchars(strip_tags($postTitle, '')); 
						$note->title_encode=base64_encode(htmlspecialchars(strip_tags($postTitle, ''))); 
						$note->slug=Str::slug(htmlspecialchars(strip_tags($postTitle, ''))); 
						$note->user_id=(string)$user->id; 
						if(!empty($channelId)){
							$note->channel_id=$channelId; 
						}else{
							$note->channel_id=0; 
						}
						$note->content=htmlspecialchars($postContent); 
						$note->tags=$tags; 
						if(!is_array($note->media)){
							$note->media=array(); 
						}
						if(count($mediaJsonDecode)>0){
							$list_medias = [];
							foreach($mediaJsonDecode as $media){
								if($media->type=="image"){
									$mediaControl= new MediaController(); 
									$getMediaUpload= $mediaControl->uploadFileFromTmp($media->type,$media->fileTmp,$media->mediaIdRandom,$media->destinationPath); 
									if($getMediaUpload!==false){
										array_push($list_medias,$getMediaUpload);
									} 
								}
							}
							$note->media= array_merge($note->media, $list_medias); 
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
						$note->price=(int)$price; 
						if(!is_array($user->attribute)){
							$user->attribute=array(); 
						}
						if(!empty($region)){
							$note->region=array(
								'region_id'=>$region, 
								'region_name'=>$regionName
							); 
							$merRegion=array('region_history'=>$region); 
							$user->attribute= array_merge($user->attribute, $merRegion); 
						}
						if(!empty($subRegion)){
							$note->subregion=array(
								'subregion_id'=>$subRegion, 
								'subregion_name'=>$subRegionName
							); 
							$merSubRegion=array('subregion_history'=>$subRegion); 
							$user->attribute= array_merge($user->attribute, $merSubRegion); 
						}
						if(!empty($address)){
							$note->address=$address; 
							$merAddress=array('address_history'=>$address); 
							$user->attribute= array_merge($user->attribute, $merAddress); 
						}
						if(!empty($name)){
							$note->user_name=$name; 
							$merName=array('name_history'=>$name); 
							$user->attribute= array_merge($user->attribute, $merName); 
						}
						if(!empty($email)){
							$note->email=$email; 
							$merEmail=array('email_history'=>$email); 
							$user->attribute= array_merge($user->attribute, $merEmail); 
						}
						if(!empty($phone)){
							$note->phone=$phone; 
							$merPhone=array('phone_history'=>$phone); 
							$user->attribute= array_merge($user->attribute, $merPhone); 
						}
						if(!empty($hiddenEmail)){
							$note->hiddenEmail=$hiddenEmail; 
							$merHiddenEmail=array('hidden_email_history'=>$hiddenEmail); 
							$user->attribute= array_merge($user->attribute, $merHiddenEmail); 
						}
						if(!empty($hiddenPhone)){
							$note->hiddenPhone=$hiddenPhone; 
							$merHiddenPhone=array('hidden_phone_history'=>$hiddenPhone); 
							$user->attribute= array_merge($user->attribute, $merHiddenPhone); 
						}
						if(empty($note->view)){
							$note->view=0; 
						}
						$note->ip=$request->ip(); 
						$note->status='active'; 
						$note->save(); 
						$user->save(); 
						return response()->json(['success'=>true,
							'message'=>'Tin của bạn đã được đăng thành công! ', 
							'noteId'=>(string)$note->id, 
							'link'=>route('post.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))), 
						]);
					}
				}else{
					return response()->json(['success'=>false,
						'message'=>'Nội dung của bạn chứa thông tin vi phạm không được phép đăng! '
					]);
				}
			}else{
				return response()->json(['success'=>false,
					'message'=>$error
				]);
			}
		}else{
			return response()->json(['success'=>false,
				'message'=>'Bạn phải đăng nhập mới có thể đăng! '
			]);
		}
	}
}