<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request; 
use Illuminate\Support\Facades\Hash;
use App\Model\Note; 
use App\Model\Note_insert; 
use App\User; 
use Theme; 
use Auth; 
use Cache; 
use DB; 
use URL; 
use Validator; 
use Carbon\Carbon;
class UserController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function profile(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$profile=Note::find($this->_parame['slug']); 
		if(!empty($profile->id)){
			if($profile->type=='user'){
				$postFromProfile = Cache::store('memcached')->remember('note_parent_user'.(string)$profile->id.'_page_'.$page,1, function() use($profile)
				{
					return DB::connection('mongodb')->collection('note')
					->select('_id','type','title','content','media','view','created_at','updated_at')
					->where('type','post')
					->where('user_id',(string)$profile->id)
					->where('status','active')
					->orderBy('updated_at','desc')
					->simplePaginate(18); 
				});
				$data=array(
					'user'=>$profile, 
					'postFromUser'=>$postFromProfile
				); 
				return Theme::view('user.profile', $data);
			}else if($profile->type=='channel'){
				$postFromProfile = Cache::store('memcached')->remember('note_parent_user'.(string)$profile->id.'_page_'.$page,1, function() use($profile)
				{
					return DB::connection('mongodb')->collection('note')
					->select('_id','type','title','content','media','view','created_at','updated_at')
					->where('type','post')
					->where('channel_id',(string)$profile->id)
					->where('status','active')
					->orderBy('updated_at','desc')
					->simplePaginate(18); 
				});
				$data=array(
					'channel'=>$profile, 
					'postFromUser'=>$postFromProfile
				); 
				return Theme::view('channel.profile', $data);
			}
		}
	}
	public function login(){
		if (Auth::check()) {
			return redirect()->to('/');
		}else{
			$data=array(); 
			return Theme::view('user.login', $data);
		}
	}
	public function loginRequest(){
		$user=Input::get('user'); 
		$password=Input::get('password'); 
		if (Auth::attempt(['type'=>'user','email' => $user, 'password' => $password], true)) {
			return response()->json(['success'=>true,
				'message'=>'Đăng nhập thành công!  ', 
				'link'=>URL::previous()
			]);
		}else{
			return response()->json(['success'=>false,
				'message'=>'Đăng nhập thất bại!  '
			]);
		}
	}
	public function register(){
		if (Auth::check()) {
			return redirect()->to('/');
		}else{
			$data=array(); 
			return Theme::view('user.register', $data);
		}
	}
	public function registerRequest(Request $request){
		if (Auth::check()) {
			return response()->json(['success'=>false,
				'message'=>'Bạn đang đăng nhập!  '
			]);
		}else{
			$checkIp=Note::where('type','ip')->where('title_encode',base64_encode($request->ip()))->first(); 
			if(!empty($checkIp->id)){
				if(!empty($checkIp->history['last_register'])){
					if(Carbon::parse($checkIp->history['last_register'])->addMinutes(30) > Carbon::now()->format('Y-m-d H:i:s')){
						return response()->json(['success'=>false,
							'message'=>'Tạo tài khoản phải cách nhau ít nhất 30 phút.'
						]);
					}
				}else{
					if(!is_array($checkIp->history)){
						$checkIp->history=array(); 
					}
					$mer=array('last_register'=>(string)Carbon::now()->format('Y-m-d H:i:s')); 
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
					'last_register'=>(string)Carbon::now()->format('Y-m-d H:i:s')
				); 
				$noteIp->save(); 
			}
			$fullName=Input::get('fullName'); 
			$email=Input::get('email'); 
			$phone=Input::get('phone'); 
			$password=Input::get('password'); 
			$password_confirmation=Input::get('password_confirmation'); 
			$messages = array(
				'alpha_dash'=>'Địa chỉ kênh chỉ là dạng chữ không dấu và số',
				'required' => 'Vui lòng nhập thông tin (*).',
				'numeric' => 'Điện thoại phải dạng số',
				'email' => 'Địa chỉ email không đúng', 
				'confirmed'=>'Nhập lại mật khẩu không chính xác'
			);
			$rules = array(
				'fullName' => 'required',
				'phone'=>'required|numeric',
				'email'=>'required|email',
				'password'=>'required|min:6|confirmed',
				'password_confirmation'=>'required|same:password',
			);
			$validator = Validator::make(Input::all(), $rules, $messages);
			if ($validator->fails())
			{
				return response()->json(['success'=>false,
					'message'=>$validator->errors()->first()
				]);
			}else{
				$checkPhone=User::where('type','user')->where('phone',$phone)->first(); 
				if(!empty($checkPhone->id)){
					return response()->json(['success'=>false,
						'message'=>'Số điện thoại '.$phone.' này đã được sử dụng!'
					]);
				}
				$checkEmail=User::where('type','user')->where('email',$email)->first(); 
				if(!empty($checkEmail->id)){
					return response()->json(['success'=>false,
						'message'=>'Địa chỉ email '.$email.' này đã được sử dụng!'
					]);
				}
				$confirmation_code=str_random(30); 
				$user= new User(); 
				$user->type='user';
				$user->name=$fullName; 
				$user->email=$email; 
				$user->phone=$phone; 
				$user->password=Hash::make($password); 
				$user->attribute=array(); 
				$user->remember_token=''; 
				$user->confirmation_code=$confirmation_code; 
				$user->send_confirm=0; 
				$user->status='pending'; 
				$user->save(); 
				if(!empty($user->id)){
					$authenData = array(
						'_id' => $user->id,
						'password' => $password,
					);
					if (Auth::attempt($authenData)) {
						return response()->json(['success'=>true,
							'message'=>'Đăng ký tài khoản thành công! '
						]);
					}
				}
			}
		}
	}
	public function logout(){
		Auth::logout();
        return redirect()->route('index',config('app.url'));
	}
}