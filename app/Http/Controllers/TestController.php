<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request; 
use Theme; 
use App\Model\Note; 
use App\Model\Jobs; 
use App\Model\Post_result; 
use App\User; 
use Cache; 
use Carbon\Carbon; 
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client; 
use AppHelper; 
use Redirect; 
use DB; 
use Auth;
class TestController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function test(Request $request){
		//$browserDetails = get_browser($_SERVER['HTTP_USER_AGENT'], true);
		dd($request->header('User-Agent'));
	}
	public function testqeqwe(Request $request){
		dd($_SERVER['HTTP_USER_AGENT']); 
		//$region=Note::where('type','category')->where('sub_type','sub_region')->first();
		//dd($region); 
		$getNote=Note::where('type','channel')->where('update_time','!=',1)->limit(100)->get(); 
		//dd($getNote);
		foreach($getNote as $note){
			dd($note); 
			if(!empty($note->address) && count($note->address)>0){
				$addArr=[]; 
				$add=[]; 
				foreach($note->address as $address){
					if(!empty($address['address'])){
						$add['address']=$address['address'];
					} 
					if(!empty($address['region'])){
						$region=Note::where('type','category')->where('sub_type','region')->where('old_id',$address['region'])->first(); 
						if(!empty($region->id)){
							$add['region']=array(
								'region_id'=>(string)$region->id, 
								'region_name'=>$region->title
							); 
						}
					}
					if(!empty($address['subregion'])){
						$subregion=Note::where('type','category')->where('sub_type','sub_region')->where('old_id',$address['subregion'])->first(); 
						if(!empty($subregion->id)){
							$add['subregion']=array(
								'subregion_id'=>(string)$subregion->id, 
								'subregion_name'=>$subregion->title
							); 
						}
					}
					array_push($addArr,$add);
				}
				$note->address=$addArr; 
			}
			$created_at=Carbon::parse($note->created_at)->format('Y-m-d H:i:s'); 
			//$updated_at=Carbon::parse($note->updated_at)->format('Y-m-d H:i:s'); 
			$note->created_at=new \MongoDB\BSON\UTCDateTime(new \DateTime($created_at)); 
			//$note->updated_at=new \MongoDB\BSON\UTCDateTime(new \DateTime($updated_at)); 
			$note->update_time=1; 
			$note->save(); 
			//dd($note);
			echo $note->title.'<p>';
			//$sec=$item['updated_at']->toDateTime()->format(DATE_RSS); 
			//$time = strtotime($date->toDateTime()->format(DATE_RSS).' UTC');
			//$dateInLocal = date("Y-m-d H:i:s", $time);
			//dd($dateInLocal); 
		}
	}
	public function test23454432345(){
		dd(Note::where('type','post')->first()); 
		$getPost=Note::where('type','post')->where('update_channel','exists',false)->limit(1000)->get(); 
		foreach($getPost as $post){
			$findChannel=Note::where('type','channel')->where('old_id',$post->channel_id)->first(); 
			if(!empty($findChannel->id)){
				$post->channel_id=(string)$findChannel->id; 
			}
			$post->update_channel=1; 
			$post->save();
		}
	}
	public function test12312321(){
		dd(Note::where('type','user')->limit(10)->get());
		$getUser=User::where('move_note','exists',false)->limit(1000)->get(); 
		foreach($getUser as $user){
			//dd($user); 
			$data=array(
				'old_id'=>(string)$user->id, 
				'type'=>'user', 
				'name'=>$user->name, 
				'email'=>$user->email, 
				'phone'=>$user->phone, 
				'password'=>$user->password, 
				'attribute'=>$user->attribute, 
				'remember_token'=>$user->remember_token, 
				'confirmation_code'=>$user->confirmation_code, 
				'status'=>$user->status, 
				'created_at'=>Carbon::parse($user->created_at)->format('Y-m-d H:i:s'), 
				'updated_at'=>Carbon::now()->format('Y-m-d H:i:s')
			); 
			Note::insertGetId($data); 
			$user->move_note=1; 
			$user->save(); 
			echo $user->name.'<p>';
		}
	}
	public function test1231231232414(){
		dd(Note::where('type','post')->limit(2)->get()); 
		$getPost=Post_result::where('move_mongo','<',2)->limit(500)->get(); 
		foreach($getPost as $post){
			$checkUser=User::where('id_old',$post->user_id)->first(); 
			//dd($post->user_id); 
			if(!empty($checkUser->id)){
				$data=array(
					'type'=>'post',
					'title'=>$post->title, 
					'title_encode'=>base64_encode($post->title), 
					'slug'=>$post->slug, 
					'user_id'=>(string)$checkUser->id, 
					'channel_id'=>$post->channel_id, 
					'category'=>json_decode($post->category,true), 
					'content'=>$post->content, 
					'media'=>json_decode($post->media,true), 
					'view'=>$post->view, 
					'created_at'=>Carbon::parse($post->created_at)->format('Y-m-d H:i:s'), 
					'updated_at'=>Carbon::parse($post->updated_at)->format('Y-m-d H:i:s'), 
					'status'=>$post->status
				); 
				Note::insertGetId($data); 
			}
			$post->move_mongo=2; 
			$post->save(); 
			echo $post->title.'<p>';
		}
		
	}
}