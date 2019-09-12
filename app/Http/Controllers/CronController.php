<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Request; 
use Theme; 
use AppHelper; 
use Storage;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client; 
use Imagick; 
use File; 
use Cache; 
use App\Model\Note; 
use App\Model\Index_note; 
use App\Model\Index_elastic;
use App\Model\Jobs; 
class CronController extends ConstructController
{
	public $_site; 
	public $_title; 
	public $_description; 
	public $_keyword; 
	public $_basicInfo; 
	public $_websiteInfo; 
	public $_semrushMetrics; 
	public $_dnsReport; 
	public $_ipAddressInfo; 
	public $_whoisRecord; 
	public $_ads_status; 
	public $_link; 
	public $_domain; 
	public $_url; 
	public $_searchResult; 
	public $_rank;
	public $_rank_country; 
	public $_country_code; 
	public function __construct(){
		parent::__construct(); 
	}
	public function index(){
		//return false; 
		if($this->_parame['type']=='index_elasticsearch'){
			//$getNote=Index_elastic::where('type','post')->where('index','!=',1)->where('status','active')->limit(500)->get(); 
			$getNote=Index_elastic::where('status','active')->where('index','!=',1)->limit(500)->get(); 
			//dd($getNote); 
			foreach($getNote as $note){
				$note->addToIndex();
				$note->index=1; 
				$note->save();
				echo $note->title.'<p>';
			} 
		}else if($this->_parame['type']=='craw_info_domain'){
			$getJob=Jobs::where('type','craw_search')->first(); 
			if(empty($getJob->id)){
				$data=array(
					'type'=>'craw_search',
					'value'=>'google', 
					'parent'=>0, 
					'updated_at'=>Carbon::now()->format('Y-m-d H:i:s'), 
				); 
				$getJob=Jobs::where('type','craw_search')->update($data, ['upsert' => true]); 
			}
			if(Carbon::parse($getJob->updated_at)->addSecond(10)->format('Y-m-d H:i:s') < Carbon::now()->format('Y-m-d H:i:s')){
				$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$getJob->save(); 
				$getNote=Index_note::where('type','domain')->where('status','pending')->where('replay','<',2)->limit(2)->get(); 
				//dd($getNote); 
				foreach($getNote as $note){ 
					$this->_domain=$note->domain; 
					$this->_link='http://'.$note->domain;
					$note->increment('replay',1); 
					if(empty($note->title)){
						$siteInfo=$this->getInfoSite(); 
						if($siteInfo!='false' && $siteInfo!='blacklist'){
							if(!empty($siteInfo['title'])){
								$note->title=(string)$siteInfo['title']; 
								$note->title_encode=base64_encode($siteInfo['title']); 
							}
							if(!empty($siteInfo['description'])){
								$note->description=$siteInfo['description']; 
							}
							if(!empty($siteInfo['keywords'])){
								$note->keywords=$siteInfo['keywords']; 
							}
							$noteMer=array(
								'image'=>$siteInfo['image'], 
								'rank'=>$siteInfo['rank'], 
								'country_code'=>$siteInfo['country_code'], 
								'rank_country'=>$siteInfo['rank_country'], 
								'craw_step'=>1
							); 
							$note->attribute= array_merge($note->attribute, $noteMer);
							$note->replay=0; 
							//$note->updated_at=new \MongoDB\BSON\UTCDateTime(Carbon::now()); 
							$note->status='active'; 
							$note->save(); 
							//$note=Index_note::find($note->id); 
						}else if($siteInfo=='blacklist'){
							$noteMer=array('craw_step'=>1,'ads'=>'disable'); 
							$note->attribute= array_merge($note->attribute, $noteMer);
							$note->replay=0; 
							//$note->updated_at=new \MongoDB\BSON\UTCDateTime(Carbon::now()); 
							$note->status='blacklist'; 
							$note->save(); 
						}else if($siteInfo=='false'){
							$noteMer=array('craw_step'=>1); 
							$note->attribute= array_merge($note->attribute, $noteMer);
							$note->replay=0; 
							//$note->updated_at=new \MongoDB\BSON\UTCDateTime(Carbon::now()); 
							$note->status='active'; 
							$note->save(); 
						}
					}
					if(empty($note->attribute['whois'])){
						try {
							$client = new Client([
								'headers' => [ 'Content-Type' => 'application/json' ]
							]);
							$checkDomain=str_replace('www.','',$note->domain); 
							$postData='{
								"domainName": "'.$checkDomain.'"
							}';
							$response = $client->request('POST', 'https://dms.inet.vn/api/public/whois/v1/whois/directly',
								 ['body' => $postData]
							);
							$getResponse=$response->getBody()->getContents(); 
							$resultDecode=json_decode($getResponse); 
							if($resultDecode->code==0){
								$noteMer=array('craw_step'=>2,'whois'=>$getResponse); 
								$note->attribute= array_merge($note->attribute, $noteMer);
								$note->replay=0; 
								//$note->updated_at=new \MongoDB\BSON\UTCDateTime(Carbon::now()); 
								$note->status='active'; 
								$note->save();  
								//$note=Index_note::find($note->id); 
							} 
						}catch (\GuzzleHttp\Exception\ServerException $e){
							//return 'false'; 
						}catch (\GuzzleHttp\Exception\BadResponseException $e){
							//return 'false'; 
						}catch (\GuzzleHttp\Exception\ClientException $e){
							//return 'false'; 
						}catch (\GuzzleHttp\Exception\ConnectException $e){
							//return 'false'; 
						} 
					}
					if(empty($note->attribute['content'])){
						$this->_site=$note; 
						$this->infoSiteOutlook(); 
					}
					if(empty($note->attribute['site_related'])){
						$this->_keyword=$note->domain; 
						$this->_searchResult=$this->getCrawFrom(); 
						if(empty($this->_searchResult['data']) || count($this->_searchResult['data'])<=0){
							$this->_searchResult=$this->getCrawFrom(); 
						}else if(empty($this->_searchResult['data']) || count($this->_searchResult['data'])<=0){
							$this->_searchResult=$this->getCrawFrom(); 
						}else if(empty($this->_searchResult['data']) || count($this->_searchResult['data'])<=0){
							$this->_searchResult=$this->getCrawFrom(); 
						}else if(empty($this->_searchResult['data']) || count($this->_searchResult['data'])<=0){
							$this->_searchResult=$this->getCrawFrom(); 
						}
						if(!empty($this->_searchResult['data']) && count($this->_searchResult['data'])){
							$siteId=[];
							$domainId=[]; 
							foreach($this->_searchResult['data'] as $search){
								if(!empty($search['linkFull'])){
									$checkExist=Index_note::where('type','site')->where('link_encode',base64_encode($search['linkFull']))->first(); 
									if(empty($checkExist->id)){
										$data=array(
											'type'=>'site', 
											'status'=>'pending'
										); 
										$noteSite=new Index_note($data); 
										$noteSite->title=$search['title']; 
										$noteSite->title_encode=base64_encode($search['title']); 
										$noteSite->link=$search['linkFull']; 
										$noteSite->link_encode=base64_encode($search['linkFull']); 
										$noteSite->description=$search['description']; 
										$noteSite->scores=0; 
										$noteSite->attribute=array(
											'domain'=>$search['domainRegister']
										); 
										$noteSite->parent=$note->id; 
										$noteSite->get_result='none'; 
										$noteSite->replay=0; 
										$noteSite->save(); 
										array_push($siteId,(string)$noteSite->id);
									}else{
										array_push($siteId,(string)$checkExist->id);
									}
								}
								if(!empty($search['domainRegister'])){
									$checkExistDomain=Index_note::where('type','domain')->where('domain_encode',base64_encode($search['domainRegister']))->first(); 
									if(empty($checkExistDomain->domain)){
										$data=array(
											'type'=>'domain', 
											'status'=>'pending'
										); 
										$noteDomain=new Index_note($data); 
										$noteDomain->domain=$search['domainRegister']; 
										$noteDomain->domain_encode=base64_encode($search['domainRegister']); 
										$noteDomain->attribute=array(); 
										$noteDomain->replay=0; 
										$noteDomain->save(); 
										array_push($domainId,(string)$noteDomain->id);
									}else{
										array_push($domainId,(string)$checkExistDomain->id);
									}
								}
							}
							$noteMer=array('craw_step'=>3,'site_related'=>array_unique($siteId),'domain_related'=>array_unique($domainId)); 
							$note->attribute= array_merge($note->attribute, $noteMer);
							//$note->updated_at=new \MongoDB\BSON\UTCDateTime(Carbon::now()); 
							$note->replay=0; 
							if($note->status!='blacklist'){
								$note->status='active';
							}
							$note->save(); 
							//$note=Index_note::find($note->id); 
						}
					}
					echo $note->domain.'<p>';
				}
			}
		}else if($this->_parame['type']=='get_dns_record_domain'){
			//return false; 
			$getNoteDomain=Index_note::where('type','domain')->where('attribute.dns_record','exists',false)->limit(10)->get(); 
			$recordArray=[]; 
			$domainArr=[]; 
			foreach($getNoteDomain as $domain){
				$replaceDomain=str_replace('www.', '',$domain->domain); 
				//dd($replaceDomain); 
				$getRecord=@dns_get_record($replaceDomain,DNS_ALL); 
				//dd($getRecord); 
				if($getRecord){
					$noteMer=array('dns_record'=>$this->utf8_converter($getRecord)); 
					$domain->attribute= array_merge($domain->attribute, $noteMer); 
					//$domain->updated_at=new \MongoDB\BSON\UTCDateTime(Carbon::now()); 
					$domain->save(); 
					if(count($getRecord)){
						foreach($getRecord as $record){
							if($record['type']=='A'){
								$checkExist=Index_note::where('type','ip')->where('title_encode',base64_encode($record['ip']))->first(); 
								if(empty($checkExist->id)){
									$url='http://ip-api.com/json/'.$record['ip'];
									$client = new Client([
										'headers' => [ 
											'Content-Type' => 'text/html',
											'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
										], 
										'connect_timeout' => '5',
										'timeout' => '5'
									]); 
									$response = $client->request('GET', $url); 
									$getResponse=$response->getBody()->getContents();
									$data=array(
										'type'=>'ip', 
										'status'=>'active'
									); 
									$noteIp=new Index_note($data); 
									$noteIp->title=$record['ip']; 
									$noteIp->title_encode=base64_encode($record['ip']); 
									$noteIp->description=$getResponse; 
									$noteIp->ip_type=$record['type']; 
									$noteIp->save(); 
								}
							}else if($record['type']=='AAAA'){
								$checkExist=Index_note::where('type','ip')->where('title_encode',base64_encode($record['ipv6']))->first(); 
								if(empty($checkExist->id)){
									$url='http://ip-api.com/json/'.$record['ipv6'];
									$client = new Client([
										'headers' => [ 
											'Content-Type' => 'text/html',
											'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
										], 
										'connect_timeout' => '5',
										'timeout' => '5'
									]); 
									$response = $client->request('GET', $url); 
									$getResponse=$response->getBody()->getContents();
									$data=array(
										'type'=>'ip', 
										'status'=>'active'
									); 
									$noteIp=new Index_note($data); 
									$noteIp->title=$record['ipv6']; 
									$noteIp->title_encode=base64_encode($record['ipv6']); 
									$noteIp->description=$getResponse; 
									$noteIp->ip_type=$record['type']; 
									$noteIp->save(); 
								}
							}
						}
					}
					echo $domain->domain.'<p>';
				}else{
					$noteMer=array('dns_record'=>[]); 
					$domain->attribute= array_merge($domain->attribute, $noteMer); 
					$domain->save(); 
				}
			}
		}else if($this->_parame['type']=='import_affiliate'){
			$getJob=Jobs::where('type','datafeed')->first(); 
			//$getJob->value="fptshop.com.vn"; 
			//$getJob->campaign="fptshop"; 
			//$getJob->total=8589; 
			//$getJob->page=1; 
			////$getJob->page_limit=172; 
			//$getJob->save(); 
			//dd($getJob); 
			//$client = new Client();
			//$response = $client->request('GET', 'https://api.accesstrade.vn/v1/datafeeds?domain=fptshop.com.vn&page=1',
			//		[
			//			'headers' => [ 'Content-Type' => 'application/json','Authorization' => 'Token krkREE14smW4HEm9_7aGbcYHOCmFSdxs']
			//		]
			//	); 
			//	$responseDecode=json_decode($response->getBody()->getContents());  
			//	dd($getJob); 
			if(empty($getJob->id)){
				$data=array(
					'type'=>'datafeed',
					'value'=>'shopee.vn', 
					'campaign'=>'shopee',
					'total'=>8589, 
					'page'=>1,
					'page_limit'=>172,
					'updated_at'=>Carbon::now()->format('Y-m-d H:i:s'), 
				); 
				$getJob=Jobs::where('type','datafeed')->update($data, ['upsert' => true]); 
			}
			if(!empty($getJob->id) && $getJob->page<=$getJob->page_limit){
				$client = new Client();
				$response = $client->request('GET', 'https://api.accesstrade.vn/v1/datafeeds?domain='.$getJob->value.'&page='.$getJob->page,
					[
						'headers' => [ 'Content-Type' => 'application/json','Authorization' => 'Token krkREE14smW4HEm9_7aGbcYHOCmFSdxs']
					]
				); 
				$responseDecode=json_decode($response->getBody()->getContents());  
				foreach($responseDecode->data as $campaign){
					$checkExist=Index_note::where('type','affiliate')->where('sub_type',$campaign->campaign)
						->where('title_encode',base64_encode($campaign->name))
						->first(); 
					if(empty($checkExist->id)){
						$data=array(
							'type'=>'affiliate', 
							'status'=>'active'
						); 
						if(!empty($campaign->image) && !empty($campaign->price) && !empty($campaign->aff_link) && !empty($campaign->url)){
							$noteProduct=new Index_note($data); 
							$noteProduct->sub_type=$campaign->campaign; 
							$noteProduct->title=$campaign->name; 
							$noteProduct->title_encode=base64_encode($campaign->name);  
							if(!empty($campaign->cate)){
								$noteProduct->category=str_replace('-', ' ', $campaign->cate); 
								$noteProduct->category_slug=$campaign->cate; 
							}
							$noteProduct->sku=$campaign->sku; 
							$noteProduct->price=$campaign->price; 
							$noteProduct->discount=$campaign->discount; 
							$noteProduct->image=$campaign->image; 
							$noteProduct->url=$campaign->url; 
							$noteProduct->deeplink=$campaign->aff_link; 
							$noteProduct->description=$campaign->desc; 
							$noteProduct->product_id=$campaign->product_id; 
							$noteProduct->save(); 
							echo $campaign->name.'<p>';
						}
					}
				}
				$getJob->increment('page',1); 
				echo $getJob->page; 
			}else{
				dd($getJob); 
			}
		}else if($this->_parame['type']=='craw_note'){
			$getJob=Jobs::where('type','craw')->first(); 
			if(empty($getJob->id)){
				$data=array(
					'type'=>'craw',
					'value'=>'category', 
					'parent'=>0, 
					'updated_at'=>Carbon::now()->format('Y-m-d H:i:s'), 
				); 
				$getJob=Jobs::where('type','craw')->update($data, ['upsert' => true]); 
			}
			if($getJob->value=='category' && Carbon::parse($getJob->updated_at)->addSecond(50)->format('Y-m-d H:i:s') < Carbon::now()->format('Y-m-d H:i:s')){
				$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$getJob->save(); 
				$getNote=Index_note::where('type','category')->where('status','pending')->where('replay','<=',3)->orderBy('created_at','asc')->limit(1)->get(); 
				$categoryId=[]; 
				foreach($getNote as $note){
					$note->increment('replay',1); 
					if($note->get_result=='none'){
						$url='http://suggestqueries.google.com/complete/search?client=chrome&q='.urlencode($note->title);  
						$client = new Client([
							'headers' => [ 
								'Content-Type' => 'text/html',
								'User-Agent' => 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n'
							], 
							'connect_timeout' => '2',
							'timeout' => '2'
						]); 
						$response = $client->request('GET', $url); 
						$content=json_decode($response->getBody()->getContents()); 
						if(!empty($content[1]) && count($content[1])>0){
							foreach($content[1] as $value){
								if(!empty($value)){
									$checkExist=Index_note::where('title_encode',base64_encode($value))->where('type','category')->first(); 
									if(empty($checkExist->id)){
										$data=array(
											'type'=>'category', 
											'status'=>'pending'
										); 
										$noteCategory=new Index_note($data); 
										$noteCategory->title=$value; 
										$noteCategory->title_encode=base64_encode($value); 
										$noteCategory->scores=0; 
										$noteCategory->attribute=array(); 
										$noteCategory->parent=$note->id; 
										$noteCategory->get_result='none'; 
										$noteCategory->replay=0; 
										$noteCategory->save(); 
										array_push($categoryId,(string)$noteCategory->id);
									}else{
										array_push($categoryId,(string)$checkExist->id);
									}
								}
							}
							$noteMer=array('category_related'=>array_unique($categoryId)); 
							if(!is_array($note->attribute)){
								$note->attribute=array(); 
							}
							$note->attribute= array_merge($note->attribute, $noteMer); 
							$note->save(); 
						}
						$this->_title=$note->title; 
						$this->_searchResult=$this->getCrawFrom(); 
						if(empty($this->_searchResult['data']) || count($this->_searchResult['data'])<=0){
							$this->_searchResult=$this->getCrawFrom(); 
						}else if(empty($this->_searchResult['data']) || count($this->_searchResult['data'])<=0){
							$this->_searchResult=$this->getCrawFrom(); 
						}else if(empty($this->_searchResult['data']) || count($this->_searchResult['data'])<=0){
							$this->_searchResult=$this->getCrawFrom(); 
						}else if(empty($this->_searchResult['data']) || count($this->_searchResult['data'])<=0){
							$this->_searchResult=$this->getCrawFrom(); 
						}
						if(!empty($this->_searchResult['data']) && count($this->_searchResult['data']) && $note->replay<=3){
							$siteId=[];
							$domainId=[]; 
							foreach($this->_searchResult['data'] as $search){
								$checkExist=Index_note::where('link_encode',base64_encode($search['linkFull']))->where('type','site')->first(); 
								if(empty($checkExist->id)){
									$data=array(
										'type'=>'site', 
										'status'=>'pending'
									); 
									$noteSite=new Index_note($data); 
									$noteSite->title=$search['title'];
									$noteSite->title_encode=base64_encode($search['title']);
									$noteSite->link=$search['linkFull'];
									$noteSite->link_encode=base64_encode($search['linkFull']);
									$noteSite->description=$search['description'];
									$noteSite->scores=0;
									$noteSite->attribute=array(
										'domain'=>$search['domainRegister']
									);
									$noteSite->parent=$note->id;
									$noteSite->get_result='none';
									$noteSite->replay=0;
									$noteSite->save(); 
									array_push($siteId,(string)$noteSite->id);
								}else{
									array_push($siteId,(string)$checkExist->id);
								}
								if(!empty($search['domainRegister'])){
									$checkExistDomain=Index_note::where('type','domain')->where('domain_encode',base64_encode($search['domainRegister']))->first(); 
									if(empty($checkExistDomain->domain)){
										$data=array(
											'type'=>'domain', 
											'status'=>'pending'
										);  
										$noteDomain=new Index_note($data); 
										$noteDomain->domain=$search['domainRegister'];
										$noteDomain->domain_encode=base64_encode($search['domainRegister']);
										$noteDomain->attribute=array();
										$noteDomain->save(); 
										array_push($domainId,(string)$noteDomain->id);
									}else{
										array_push($domainId,(string)$checkExistDomain->id);
									}
								}
							}
							$noteMer=array('site_related'=>array_unique($siteId),'domain_related'=>array_unique($domainId)); 
							if(!is_array($note->attribute)){
								$note->attribute=array(); 
							}
							$note->attribute= array_merge($note->attribute, $noteMer);
							$note->replay=0; 
							$note->get_result='site'; 
							$note->status='craw'; 
							$note->save(); 
							$getJob->value='craw_site'; 
							$getJob->parent=$note->id; 
							$getJob->save(); 
						}else{
							$note->replay=0; 
							$note->get_result='image'; 
							$note->status='craw'; 
							$note->save(); 
							$getJob->value='craw_site'; 
							$getJob->parent=$note->id; 
							$getJob->save();
						}
					}
					echo 'get Subget and site '.$note->title.'<p>';
				}
			}else if($getJob->value=='craw_site' && Carbon::parse($getJob->updated_at)->addSecond(50)->format('Y-m-d H:i:s') < Carbon::now()->format('Y-m-d H:i:s')){
				$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$getJob->save(); 
				$note=Index_note::find($getJob->parent); 
				if(!empty($note->id)){
					if($note->get_result=='site' && $note->replay<=10){
						$note->increment('replay',1); 
						$getNoteSite=Index_note::where('type','site')->where('parent',$note->id)->where('replay','<=',3)->where('status','pending')->limit(2)->get(); 
						if(count($getNoteSite)>0){
							foreach($getNoteSite as $noteSite){
								$noteSite->increment('replay',1); 
								if($noteSite->replay<=3){
									$this->_domain=$noteSite->attribute['domain']; 
									$this->_link=$noteSite->link; 
									$siteInfo=$this->getInfoSite(); 
									if($siteInfo!='false' && $siteInfo!='blacklist'){
										if(!empty($siteInfo['title'])){
											$noteSite->title=(string)$siteInfo['title']; 
											$noteSite->title_encode=base64_encode($siteInfo['title']); 
										}
										if(!empty($siteInfo['description'])){
											$noteSite->description=$siteInfo['description']; 
										}
										if(!empty($siteInfo['keywords'])){
											$noteSite->keywords=$siteInfo['keywords']; 
										}
										$noteMer=array(
											'domain'=>$noteSite->attribute['domain'], 
											'image'=>$siteInfo['image'], 
											'rank'=>$siteInfo['rank'], 
											'country_code'=>$siteInfo['country_code'], 
											'rank_country'=>$siteInfo['rank_country'], 
										);
										$noteSite->attribute= array_merge($noteSite->attribute, $noteMer);
										$noteSite->replay=0; 
										$noteSite->get_result='from_site'; 
										$noteSite->status='active'; 
										$noteSite->save(); 
									}else if($siteInfo=='blacklist'){
										$noteSite->status='blacklist'; 
										$noteSite->save(); 
									}else if($siteInfo=='false'){
										$noteSite->replay=0; 
										$noteSite->get_result='from_search'; 
										$noteSite->status='active'; 
										$noteSite->save(); 
									}
								}else{
									$noteSite->replay=0; 
									$noteSite->get_result='from_search'; 
									//$noteSite->updated_at=new \MongoDB\BSON\UTCDateTime(Carbon::now()); 
									$noteSite->status='active'; 
									$noteSite->save(); 
								}
								echo 'get info site '.$noteSite->title.'<p>';
							}
						}else{
							$note->replay=0; 
							$note->get_result='image'; 
							$note->save(); 
						}
					}else if($note->get_result=='image' && $note->replay<=10){
						$note->increment('replay',1); 
						$getNoteImage=Index_note::where('type','image')->where('parent',$note->id)->where('status','active')->get(); 
						if(count($getNoteImage)<=0){
							$url='https://www.google.com.vn/search?q='.urlencode($note->title).'&tbm=isch';  
							$client = new Client([
								'headers' => [ 
									'Content-Type' => 'text/html',
									'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
								], 
								'connect_timeout' => '2',
								'timeout' => '2'
							]); 
							$response = $client->request('GET', $url); 
							$getResponse=$response->getBody()->getContents();
							$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
							$doc = new \DOMDocument;
							@$doc->loadHTML($dataConvertUtf8);    
							$xpath = new \DOMXpath($doc); 
							$nodeList = $xpath->query('//div[@id="rg"]'); 
							$imageId=[];
							$listResult=$nodeList->item(0); 
							$metas = $listResult->getElementsByTagName('div');
							for ($i = 0; $i < $metas->length; $i++)
							{
								$meta = $metas->item($i);
								if($meta->getAttribute('class') == 'rg_meta notranslate'){
									$decodeItem=json_decode($meta->nodeValue); 
									if(!empty($decodeItem->ou)){
										$checkExist=Index_note::where('link_encode',base64_encode($decodeItem->ou))->where('type','image')->first(); 
										if(empty($checkExist->id)){
											if(@getimagesize($decodeItem->ou)){
												$dir=AppHelper::instance()->makeDir('media/img'); 
												$name = str_random(5).'-'.time(); 
												$extension = substr($decodeItem->ou, strrpos($decodeItem->ou, '.') + 1); 
												$handle = fopen($decodeItem->ou, 'rb'); 
												$img = new Imagick(); 
												$img->readImageFile($handle); 
												$filename=$name.'.'.mb_strtolower($img->getImageFormat()); 
												$img->writeImage('tmp/'.$filename);
												$file_path = 'tmp/'.$filename; 
												$file_path_thumb = 'tmp/thumb-'.$filename;
												$identifyImage=$img->identifyImage(); 
												$demention = getimagesize($file_path);
												$imgThumbnail = new Imagick($file_path);
												$widthMd=720; $heightMd=480; 
												$widthXs=210; $heightXs=118; 
												if($identifyImage['mimetype'] == "image/gif"){
													$imgThumbnail = $imgThumbnail->coalesceImages(); 
													foreach ($imgThumbnail as $frame) { 
														$frame->scaleImage($widthMd,$heightMd,true); 
														$frame->setImageBackgroundColor('white');
														$w = $frame->getImageWidth();
														$h = $frame->getImageHeight();
														$frame->extentImage($widthMd,$heightMd,($w-$widthMd)/2,($h-$heightMd)/2);
													} 
													$imgThumbnail = $imgThumbnail->deconstructImages(); 
												}else{
													$imgThumbnail->scaleImage($widthMd,$heightMd,true); 
													$imgThumbnail->setImageBackgroundColor('white');
													$w = $imgThumbnail->getImageWidth();
													$h = $imgThumbnail->getImageHeight();
													$imgThumbnail->extentImage($widthMd,$heightMd,($w-$widthMd)/2,($h-$heightMd)/2);
												}
												$imgThumbnail->setImageCompression(Imagick::COMPRESSION_JPEG);
												$imgThumbnail->setImageCompressionQuality(95); 
												$imgThumbnail->writeImage(); 
												
												$imgXS=new Imagick($file_path); 
												if($identifyImage['mimetype'] == "image/gif"){
													$imgXS = $imgXS->coalesceImages(); 
													foreach ($imgXS as $frame) { 
														$frame->scaleImage($widthXs,$heightXs,true); 
														$frame->setImageBackgroundColor('white');
														$w = $frame->getImageWidth();
														$h = $frame->getImageHeight();
														$frame->extentImage($widthXs,$heightXs,($w-$widthXs)/2,($h-$heightXs)/2);  
													} 
													$imgXS = $imgXS->deconstructImages(); 
												}else{
													$imgXS->scaleImage($widthXs,$heightXs,true); 
													$imgXS->setImageBackgroundColor('white');
													$w = $imgXS->getImageWidth();
													$h = $imgXS->getImageHeight();
													$imgXS->extentImage($widthXs,$heightXs,($w-$widthXs)/2,($h-$heightXs)/2); 
												}
												$imgXS->setImageCompression(Imagick::COMPRESSION_JPEG);
												$imgXS->setImageCompressionQuality(95); 
												$imgXS->writeImages('tmp/thumb-'.$filename,true);
												Storage::disk('s3')->put($dir.'/thumb-'.$filename, file_get_contents($file_path_thumb)); 
												Storage::disk('s3')->put($dir.'/'.$filename, file_get_contents($file_path)); 
												$image='//cdn.cungcap.net/'.$dir.'/'.$filename; 
												$image_thumb='//cdn.cungcap.net/'.$dir.'/thumb-'.$filename; 
												File::delete($file_path); 
												File::delete($file_path_thumb); 
												$data=array(
													'type'=>'image', 
													'status'=>'active'
												); 
												$noteImage=new Index_note($data); 
												$noteImage->title=$decodeItem->pt;
												$noteImage->title_encode=base64_encode($decodeItem->pt);
												$noteImage->link=$decodeItem->ou;
												$noteImage->link_encode=base64_encode($decodeItem->ou);
												$noteImage->scores=0;
												$noteImage->attribute=array(
													'image'=>$image, 
													'thumb'=>$image_thumb, 
													'craw_content'=>$meta->nodeValue, 
													'domain'=>$decodeItem->rh
												);
												$noteImage->parent=$note->id;
												$noteImage->get_result='success';
												$noteImage->replay=0;
												$noteImage->save(); 
												array_push($imageId,(string)$noteImage->id);
											}
										}else{
											array_push($imageId,(string)$checkExist->id);
										}
									} 
								}
							}
							$noteMer=array('image_related'=>array_unique($imageId)); 
							$note->attribute= array_merge($note->attribute, $noteMer); 
							$note->save(); 
							echo 'get image '.$note->title.'<p>';
						}else{
							$note->replay=0; 
							$note->get_result='video'; 
							$note->save(); 
						}
					}else if($note->get_result=='video' && $note->replay<=10){
						$note->increment('replay',1); 
						$getNoteVideo=Index_note::where('type','video')->where('parent',$note->id)->where('status','active')->get(); 
						if(count($getNoteVideo)<=0){
							$title=''; 
							$description=''; 
							$videoId=[];
							$url='https://www.youtube.com/results?search_query='.urlencode($note->title);  
							$client = new Client([
								'headers' => [ 
									'Content-Type' => 'text/html',
									'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
								], 
								'connect_timeout' => '2',
								'timeout' => '2'
							]); 
							$response = $client->request('GET', $url); 
							$getResponse=$response->getBody()->getContents();
							$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
							//echo $dataConvertUtf8; exit; 
							$doc = new \DOMDocument;
							@$doc->loadHTML($dataConvertUtf8);    
							$xpath = new \DOMXpath($doc); 
							$nodeList = $xpath->query('//ol[@class="item-section"]'); 
							$html=$doc->saveHtml($nodeList->item(0)); 
							//echo $html;exit; 
							$listResult=$nodeList->item(0); 
							$metas = $listResult->getElementsByTagName('li');
							for ($i = 0; $i < $metas->length; $i++)
							{	$meta = $metas->item($i); 
								$getElement=$meta->getElementsByTagName('div'); 
								for ($t = 0; $t < $getElement->length; $t++)
								{
									$element=$getElement->item($t); 
									if($element->getAttribute('class') == 'yt-lockup yt-lockup-tile yt-lockup-video vve-check clearfix'){
										$getItem=$element->getElementsByTagName('h3'); 
										for ($y = 0; $y < $getItem->length; $y++)
										{
											$item=$getItem->item($y); 
											if($item->getAttribute('class') == 'yt-lockup-title '){
												$getLink=$item->getElementsByTagName('a'); 
												parse_str($getLink->item(0)->getAttribute('href'), $query ); 
												if(!empty($query['/watch?v'])){
													$idYoutube=$query['/watch?v']; 
													$this->_title=$getLink->item(0)->nodeValue; 
												}
											}
										}
										$getDescription=$element->getElementsByTagName('div'); 
										for ($k = 0; $k < $getDescription->length; $k++)
										{
											$des=$getDescription->item($k); 
											if($des->getAttribute('class') == 'yt-lockup-description yt-ui-ellipsis yt-ui-ellipsis-2'){
												$this->_description=$des->nodeValue; 
												//echo $description.'<p>';
											}
										}
									}
									if(!empty($idYoutube) && !empty($this->_title)){
										$checkExist=Index_note::where('yid',$idYoutube)->where('type','video')->first(); 
										if(empty($checkExist->id)){
											if($element->getAttribute('class') == 'search-refinements'){
												$getKeySearch=$element->getElementsByTagName('div'); 
												for ($z = 0; $z < $getKeySearch->length; $z++)
												{
													$itemKey=$getKeySearch->item($z); 
													if($itemKey->getAttribute('class') == 'search-refinement'){
														if(!empty($itemKey->nodeValue)){
															$checkExist=Index_note::where('title_encode',base64_encode($itemKey->nodeValue))->where('type','category')->first(); 
															if(empty($checkExist->id)){
																$data=array(
																	'type'=>'category', 
																	'status'=>'pending'
																); 
																$noteCategory=new Index_note($data); 
																$noteCategory->title=$itemKey->nodeValue;
																$noteCategory->title_encode=base64_encode($itemKey->nodeValue);
																$noteCategory->scores=0;
																$noteCategory->attribute=array();
																$noteCategory->parent=$note->id;
																$noteCategory->get_result='none';
																$noteCategory->replay=0;
																$noteCategory->save(); 
															}
														}
													}
												}
											}
											/*$listStream=array(); 
											parse_str(file_get_contents('http://www.youtube.com/get_video_info?video_id='.$idYoutube), $video_data);
											$streams = $video_data['url_encoded_fmt_stream_map'];
											$streams = explode(',',$streams);
											$counter = 1; 
											foreach ($streams as $streamdata) {
												parse_str($streamdata,$streamdata); 
												array_push($listStream,$streamdata);
												foreach ($streamdata as $key => $value) {
													if ($key == "url") {
														//echo $value; 
														$value = urldecode($value);
														//printf("<strong>%s:</strong> <a href='%s'>video link</a><br/>", $key, $value);
													} else {
														//printf("<strong>%s:</strong> %s<br/>", $key, $value);
													}
												}
												$counter = $counter+1;
											}*/
											$imageUrl='https://img.youtube.com/vi/'.$idYoutube.'/0.jpg'; 
											$dir=AppHelper::instance()->makeDir('media/img'); 
											$name = str_random(5).'-'.time(); 
											$handle = fopen($imageUrl, 'rb'); 
											$img = new Imagick(); 
											$img->readImageFile($handle); 
											$filename=$name.'.'.mb_strtolower($img->getImageFormat()); 
											$img->writeImage('tmp/'.$filename);
											$file_path = 'tmp/'.$filename; 
											$file_path_thumb = 'tmp/thumb-'.$filename;
											$identifyImage=$img->identifyImage(); 
											$demention = getimagesize($file_path);
											$imgThumbnail = new Imagick($file_path);
											$widthMd=720; $heightMd=480; 
											$widthXs=210; $heightXs=118; 
											if($identifyImage['mimetype'] == "image/gif"){
												$imgThumbnail = $imgThumbnail->coalesceImages(); 
												foreach ($imgThumbnail as $frame) { 
													$frame->scaleImage($widthMd,$heightMd,true); 
													$frame->setImageBackgroundColor('white');
													$w = $frame->getImageWidth();
													$h = $frame->getImageHeight();
													$frame->extentImage($widthMd,$heightMd,($w-$widthMd)/2,($h-$heightMd)/2);
												} 
												$imgThumbnail = $imgThumbnail->deconstructImages(); 
											}else{
												$imgThumbnail->scaleImage($widthMd,$heightMd,true); 
												$imgThumbnail->setImageBackgroundColor('white');
												$w = $imgThumbnail->getImageWidth();
												$h = $imgThumbnail->getImageHeight();
												$imgThumbnail->extentImage($widthMd,$heightMd,($w-$widthMd)/2,($h-$heightMd)/2);
											}
											$imgThumbnail->setImageCompression(Imagick::COMPRESSION_JPEG);
											$imgThumbnail->setImageCompressionQuality(95); 
											$imgThumbnail->writeImage(); 
											$imgXS=new Imagick($file_path); 
											if($identifyImage['mimetype'] == "image/gif"){
												$imgXS = $imgXS->coalesceImages(); 
												foreach ($imgXS as $frame) { 
													$frame->scaleImage($widthXs,$heightXs,true); 
													$frame->setImageBackgroundColor('white');
													$w = $frame->getImageWidth();
													$h = $frame->getImageHeight();
													$frame->extentImage($widthXs,$heightXs,($w-$widthXs)/2,($h-$heightXs)/2);  
												} 
												$imgXS = $imgXS->deconstructImages(); 
											}else{
												$imgXS->scaleImage($widthXs,$heightXs,true); 
												$imgXS->setImageBackgroundColor('white');
												$w = $imgXS->getImageWidth();
												$h = $imgXS->getImageHeight();
												$imgXS->extentImage($widthXs,$heightXs,($w-$widthXs)/2,($h-$heightXs)/2); 
											}
											$imgXS->setImageCompression(Imagick::COMPRESSION_JPEG);
											$imgXS->setImageCompressionQuality(95); 
											$imgXS->writeImages('tmp/thumb-'.$filename,true);
											Storage::disk('s3')->put($dir.'/thumb-'.$filename, file_get_contents($file_path_thumb)); 
											Storage::disk('s3')->put($dir.'/'.$filename, file_get_contents($file_path)); 
											$image='//cdn.cungcap.net/'.$dir.'/'.$filename; 
											$image_thumb='//cdn.cungcap.net/'.$dir.'/thumb-'.$filename; 
											File::delete($file_path); 
											File::delete($file_path_thumb); 
											$data=array(
												'type'=>'video', 
												'status'=>'active'
											); 
											$noteVideo=new Index_note($data); 
											$noteVideo->title=$this->_title; 
											$noteVideo->title_encode=base64_encode($this->_title); 
											$noteVideo->description=$this->_description; 
											$noteVideo->yid=$idYoutube; 
											$noteVideo->image=$image; 
											$noteVideo->thumb=$image_thumb; 
											$noteVideo->scores=0; 
											$noteVideo->attribute=array( 
												//'stream'=>$listStream
											); 
											$noteVideo->parent=$note->id; 
											$noteVideo->get_result='none'; 
											$noteVideo->replay=0; 
											$noteVideo->save(); 
											array_push($videoId,(string)$noteVideo->id);
										}else{
											array_push($videoId,(string)$checkExist->id);
										}
									}
								}
							}
							$noteMer=array('video_related'=>array_unique($videoId)); 
							$note->attribute= array_merge($note->attribute, $noteMer); 
							$note->save(); 
							echo 'get video '.$note->title.'<p>';
						}else{
							$getJob->parent='';
							$getJob->value='category'; 
							$getJob->save(); 
							$note->replay=0; 
							$note->get_result='success';
							$note->status='active'; 
							$note->save(); 
						}
					}else{
						$getJob->parent='';
						$getJob->value='category'; 
						$getJob->save(); 
						$note->get_result='faild';
						$note->status='faild'; 
						$note->save(); 
					}
				}
			}
		}
	}
	public function getInfoSite(){
		$title=''; 
		$description=''; 
		$keywords=''; 
		$image=''; 
		$rank='';
		$country_code=''; 
		$rank_country=''; 
		$status='true'; 
		/*$urlRank='http://data.alexa.com/data?cli=10&url='.$this->_domain; 
		$getXml=simplexml_load_file($urlRank); 
		if(!empty($getXml->SD->POPULARITY['TEXT'])){
			$rank=$getXml->SD->POPULARITY['TEXT']; 
		}
		if(!empty($getXml->SD->COUNTRY['CODE'])){
			$country_code=$getXml->SD->COUNTRY['CODE']; 
		}
		if(!empty($getXml->SD->COUNTRY['RANK'])){
			$rank_country=$getXml->SD->COUNTRY['RANK']; 
		}*/
		try {
			$client = new Client([
				'headers' => [ 
					'Content-Type' => 'text/html',
					'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
				],  
				'connect_timeout' => '5',
				'timeout' => '5'
			]);
			$response = $client->request('GET', $this->_link); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8);   
			$nodes = $doc->getElementsByTagName('title');
			if($nodes->length>0){
				$title = $nodes->item(0)->nodeValue;
			} 
			$metas = $doc->getElementsByTagName('meta');
			for ($i = 0; $i < $metas->length; $i++)
			{
				$meta = $metas->item($i);
				if($meta->getAttribute('name') == 'description')
					$description = $meta->getAttribute('content');
				if($meta->getAttribute('name') == 'keywords')
					$keywords = $meta->getAttribute('content');
				if($meta->getAttribute('property') == 'og:image')
					$image = $meta->getAttribute('content');
			}
			if(!empty($title))
			{
				if(!AppHelper::instance()->checkBlacklistWord($title)){
					$status='false';
				}
				
			}
			if(!empty($description))
			{
				if(!AppHelper::instance()->checkBlacklistWord($description)){
					$status='false';
				}
			}
			if(!empty($keywords))
			{
				if(!AppHelper::instance()->checkBlacklistWord($keywords)){
					$status='false';
				}
			}
			if($status=='true'){
				return array(
					'title'=>iconv('UTF-8', 'UTF-8//IGNORE', str_replace("\n", "", str_replace("\r", "", $title))), 
					'description'=>iconv('UTF-8', 'UTF-8//IGNORE', str_replace("\n", "", str_replace("\r", "", $description))), 
					'image'=>$image, 
					'rank'=>(string)$rank, 
					'country_code'=>(string)$country_code, 
					'rank_country'=>(string)$rank_country
				);  
			}else{
				return 'blacklist'; 
			}
		}catch (\GuzzleHttp\Exception\ServerException $e){
			return 'false';
		}catch (\GuzzleHttp\Exception\BadResponseException $e){
			return 'false';
		}catch (\GuzzleHttp\Exception\ClientException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\ConnectException $e){
			return 'false';
		}catch (\GuzzleHttp\Exception\RequestException $e){
			return 'false';
		}
	}
	public function infoSiteOutlook(){
		try {
			$client = new Client([
				'headers' => [ 
					'Content-Type' => 'text/html',
					'User-Agent' => 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n'
				], 
				['allow_redirects' => true], 
				'connect_timeout' => '2',
				'timeout' => '2'
			]);
			$url='http://'.$this->_site->domain.'.websiteoutlook.com/'; 
			$response = $client->request('GET', $url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse; 
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);    
			$xpath = new \DOMXpath($doc); 
			$nodes = $doc->getElementsByTagName('title');
			//get and display what you need:
			$this->_title = $nodes->item(0)->nodeValue; 
			$metas = $doc->getElementsByTagName('meta');
			for ($i = 0; $i < $metas->length; $i++)
			{
				$meta = $metas->item($i);
				if($meta->getAttribute('name') == 'description')
					$this->_description = $meta->getAttribute('content');
				if($meta->getAttribute('name') == 'keywords')
					$this->_keyword = $meta->getAttribute('content');
			}
			foreach ($xpath->evaluate('//div[@id="basic"]/div[2][contains(concat (" ", normalize-space(@class), " "), " panel-body ")]/div[2]/table[contains(concat (" ", normalize-space(@class), " "), " table table-condensed ")] | //html/body/div[2]/div[2]/div[1]/div[2]/div/div[2]/div[2]/table') as $node) {
				$this->_basicInfo=$doc->saveHtml($node); 
				$this->_basicInfo = preg_replace('/<form.*>[^>]*.*[^>]*>/i','',$this->_basicInfo); 
			}
			foreach ($xpath->evaluate('//div[@id="website"]/div[2][contains(concat (" ", normalize-space(@class), " "), " panel-body ")]/dl[contains(concat (" ", normalize-space(@class), " "), " dl-horizontal ")] | //html/body/div[2]/div[2]/div[1]/div[3]/div/div[2]/dl') as $node) {
				$this->_websiteInfo=$doc->saveHtml($node); 
			}
			foreach ($xpath->evaluate('//div[@id="sem"]/div[2][contains(concat (" ", normalize-space(@class), " "), " panel-body ")] | //html/body/div[2]/div[2]/div[1]/div[4]/div/div[2]') as $node) {
				$this->_semrushMetrics=$doc->saveHtml($node); 
			}
			foreach ($xpath->evaluate('//div[@id="dns"]/div[2][contains(concat (" ", normalize-space(@class), " "), " panel-body ")] | //html/body/div[2]/div[2]/div[1]/div[5]/div/div[2]') as $node) {
				$this->_dnsReport=$doc->saveHtml($node); 
			}
			foreach ($xpath->evaluate('//div[@id="geo"]/div[2][contains(concat (" ", normalize-space(@class), " "), " panel-body ")] | //html/body/div[2]/div[2]/div[1]/div[6]/div/div[2]') as $node) {
				$this->_ipAddressInfo=$doc->saveHtml($node); 
			}
			foreach ($xpath->evaluate('//div[@id="whois"]/div[2][contains(concat (" ", normalize-space(@class), " "), " panel-body ")] | //html/body/div[2]/div[2]/div[1]/div[7]/div/div[2]') as $node) {
				$this->_whoisRecord=$doc->saveHtml($node); 
			}
			$pos = strpos($dataConvertUtf8, 'adsbygoogle'); 
			if ($pos === false) {
				$this->_ads_status='disable';
			}else{
				$this->_ads_status=='active'; 
			}
			$data=array(
				'title'=>$this->_title, 
				'description'=>$this->_description, 
				'keyword'=>$this->_keyword, 
				'basic_info'=>$this->_basicInfo, 
				'website_info'=>$this->_websiteInfo, 
				'semrush_metrics'=>$this->_semrushMetrics, 
				'dns_report'=>$this->_dnsReport, 
				'ip_address_info'=>$this->_ipAddressInfo, 
				'whois_record'=>$this->_whoisRecord
			); 
			if(empty($this->_site->title)){
				$this->_site->title=$this->_title; 
			}
			if(empty($this->_site->description)){
				$this->_site->description=$this->_description; 
			}
			if(empty($this->_site->keywords)){
				$this->_site->keywords=$this->_keyword; 
			}
			$noteMer=array(
				'rank'=>(string)$this->_rank, 
				'rank_country'=>(string)$this->_rank_country, 
				'country_code'=>(string)$this->_country_code, 
				'ads'=>$this->_ads_status, 
				'content'=>json_encode($data)
			);
			$this->_site->attribute= array_merge((array)$this->_site->attribute, $noteMer);
			$this->_site->status='active';  
			$this->_site->save(); 
			$this->_site=Index_note::find($this->_site->id); 
			unset($client);
			return $this->_site;
		}catch (\GuzzleHttp\Exception\ServerException $e){
			$this->_site->save(); 
			$this->_site=Index_note::find($this->_site->id); 
			//unset($client);
			return $this->_site;
		}catch (\GuzzleHttp\Exception\BadResponseException $e){
			$this->_site->save(); 
			$this->_site=Index_note::find($this->_site->id); 
			//unset($client);
			return $this->_site;
		}catch (\GuzzleHttp\Exception\ClientException $e){
			$this->_site->save(); 
			$this->_site=Index_note::find($this->_site->id); 
			//unset($client);
			return $this->_site; 
		}catch (\GuzzleHttp\Exception\ConnectException $e){
			$this->_site->save(); 
			$this->_site=Index_note::find($this->_site->id); 
			//unset($client);
			return $this->_site;
		}catch (\GuzzleHttp\Exception\RequestException $e){
			$this->_site->save(); 
			$this->_site=Index_note::find($this->_site->id); 
			//unset($client);
			return $this->_site;
		}
		$this->_site->save(); 
		$this->_site=Index_note::find($this->_site->id); 
		//unset($client);
		return $this->_site;
	}
	public function getCrawFrom()
    {
		$data=[]; 
		$jobCrawFrom=Jobs::where('type','craw_from_another')->first(); 
		if(empty($jobCrawFrom->id)){
			$data=array(
				'type'=>'craw_from_another',
				'value'=>'cungcapnet.000webhostapp.com', 
				'from'=>'google',
				'updated_at'=>Carbon::now()->format('Y-m-d H:i:s'), 
			); 
			$jobCrawFrom=Jobs::where('type','craw_from_another')->update($data, ['upsert' => true]); 
		}
		//$jobCrawFrom->value='150.95.112.139'; 
		//$jobCrawFrom->from='bing'; 
		//$jobCrawFrom->save(); 
		//dd($jobCrawFrom); 
		if($jobCrawFrom->value=='cungcapnet.000webhostapp.com'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='https://cungcapnet.000webhostapp.com/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='https://cungcapnet.000webhostapp.com/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='150.95.112.139'; 
				$jobCrawFrom->from='google'; 
				$jobCrawFrom->save(); 
				$this->_url='https://cungcapnet.000webhostapp.com/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='150.95.112.139'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://150.95.112.139/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://150.95.112.139/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='163.44.206.117'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='google'; 
				$jobCrawFrom->save(); 
				$this->_url='http://150.95.112.139/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='163.44.206.117'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://163.44.206.117/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://163.44.206.117/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='150.95.104.215'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='google'; 
				$jobCrawFrom->save(); 
				$this->_url='http://163.44.206.117/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='150.95.104.215'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://150.95.104.215/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://150.95.104.215/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='cungcap.esy.es'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://150.95.104.215/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='cungcap.esy.es'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.esy.es/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.esy.es/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='cungcap.16mb.com'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.esy.es/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='cungcap.16mb.com'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.16mb.com/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.16mb.com/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='cungcap.hol.es'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.16mb.com/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='cungcap.hol.es'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.hol.es/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.hol.es/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='cungcap.pe.hu'; 
				//$jobCrawFrom->from='bing'; 
				//$jobCrawFrom->save(); 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.hol.es/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='cungcap.pe.hu'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.pe.hu/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.pe.hu/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='cungcap.890m.com'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.pe.hu/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='cungcap.890m.com'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.890m.com/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.890m.com/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='cungcap.xyz'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='google'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.pe.hu/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='cungcap.xyz'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.xyz/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.xyz/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='crviet.000webhostapp.com'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='google'; 
				$jobCrawFrom->save(); 
				$this->_url='http://cungcap.xyz/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}else if($jobCrawFrom->value=='crviet.000webhostapp.com'){
			if($jobCrawFrom->from=='google'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='google'; 
				$jobCrawFrom->from='yahoo'; 
				$jobCrawFrom->save(); 
				$this->_url='http://crviet.000webhostapp.com/get.php?k='.urlencode($this->_keyword).'&from=google'; 
				$data['data']=$this->getSearchGoogleFrom(); 
				return $data; 
			}else if($jobCrawFrom->from=='yahoo'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='yahoo'; 
				$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$this->_url='http://crviet.000webhostapp.com/get.php?k='.urlencode($this->_keyword).'&from=yahoo'; 
				$data['data']=$this->getSearchYahooFrom();
				return $data; 
			}else if($jobCrawFrom->from=='bing'){
				$data['site']=$jobCrawFrom->value; 
				$data['from']='bing'; 
				$jobCrawFrom->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
				$jobCrawFrom->value='cungcapnet.000webhostapp.com'; 
				//$jobCrawFrom->from='bing'; 
				$jobCrawFrom->save(); 
				$jobCrawFrom->from='google'; 
				$jobCrawFrom->save(); 
				$this->_url='http://crviet.000webhostapp.com/get.php?k='.urlencode($this->_keyword).'&from=bing'; 
				$data['data']=$this->getSearchBingFrom();
				return $data; 
			}
		}
	}
	public function getSearchGoogleFrom()
    {
		$listArray=[]; 
		$itemSearch=[]; 
		try {
			$client = new Client([
				'headers' => [ 'Content-Type' => 'text/html' ], 
				'connect_timeout' => '5',
				'timeout' => '5'
			]);
			$response = $client->request('GET', $this->_url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			//$dataConvertUtf8 = iconv('UTF-8', 'UTF-8//IGNORE', $dataConvertUtf8);
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8);    
			$xpath = new \DOMXpath($doc); 
			$domainRegister=''; 
			foreach ($xpath->evaluate('//div[@id="search"]') as $node) {
				$doc->saveHtml($node); 
				$metas = $doc->getElementsByTagName('div');
				for ($i = 0; $i < $metas->length; $i++)
				{
					$meta = $metas->item($i);
					if($meta->getAttribute('class') == 'g'){
						$getTitle=$meta->getElementsByTagName('h3'); 
						$getImage=$meta->getElementsByTagName('div'); 
						$getDescription=$meta->getElementsByTagName('span'); 
						$getLink=$meta->getElementsByTagName('a'); 
						if($getLink->length>0 && $getTitle->length>0 && $getDescription->length>0){
							if(!empty($getTitle->item(0)) && $getTitle->item(0)->getAttribute('class') == 'r'){
								if(!empty($getTitle->item(0)->nodeValue)){
									$title=$getTitle->item(0)->nodeValue; 
									$itemSearch['title']=$title; 
								}
								if(!empty($getLink->item(0)) && !empty($getLink->item(0)->getAttribute('href'))){
									parse_str($getLink->item(0)->getAttribute('href'), $query ); 
									if(!empty($query['/url?q'])){
										$itemSearch['linkFull']=$query['/url?q']; 
										$parsedUrl=parse_url($query['/url?q']); 
										$domain=$this->_rulesDomain->resolve($parsedUrl['host']); 
										if(!empty($domain->getRegistrableDomain())){
											$itemSearch['domainRegister']=$domain->getRegistrableDomain(); 
										}
									}
								}
							}
							if(!empty($getImage->length>0) && $getImage->item(0)->getAttribute('class') == 'th'){
								$getImageLink=$meta->getElementsByTagName('img'); 
								if($getImageLink->length>0 && !empty($getImageLink->item(0)->getAttribute('src'))){
									$image=$getImageLink->item(0)->getAttribute('src'); 
									$itemSearch['image']=$image; 
								}else{
									$itemSearch['image']=''; 
								}
							}else{
								$itemSearch['image']=''; 
							}
							for ($y = 0; $y < $getDescription->length; $y++)
							{
								$metagetDescription = $getDescription->item($y);
								if($metagetDescription->getAttribute('class') == 'st'){
									$description = $metagetDescription->nodeValue; 
									$itemSearch['description']=str_replace("\n", "", str_replace("\r", "", $description)); 
								} 
							}
							$status='true'; 
							if(!empty($itemSearch['title'])){
								if(!AppHelper::instance()->checkBlacklistWord($itemSearch['title'])){
									$status='false';
								}
							}
							if(!empty($itemSearch['description']))
							{
								if(!AppHelper::instance()->checkBlacklistWord($itemSearch['description'])){
									$status='false';
								}
							}
							if(!empty($itemSearch['domainRegister']))
							{
								if($itemSearch['domainRegister']=='cungcap.net' || $itemSearch['domainRegister']=='crviet.com' || $itemSearch['domainRegister']=='cungcap.vn'){
									$status='false'; 
								}
							}
							if($status=='true'){
								array_push($listArray,$itemSearch);
							}
						}
					}
				}
			}
			unset($client);
			return $listArray; 
		}catch (\GuzzleHttp\Exception\ServerException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\BadResponseException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\ClientException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\ConnectException $e){
			return 'false'; 
		}
	}
	public function getSearchYahooFrom()
    {
		//return 'false';
		$listArray=[]; 
		$itemSearch=[];
		try {
			$client = new Client([
				'headers' => [ 'Content-Type' => 'text/html' ], 
				'connect_timeout' => '5',
				'timeout' => '5'
			]);
			$response = $client->request('GET', $this->_url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			//$dataConvertUtf8 = iconv('UTF-8', 'UTF-8//IGNORE', $dataConvertUtf8);
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8);    
			$xpath = new \DOMXpath($doc);  
			foreach ($xpath->evaluate('//div[@id="web"]') as $node) {
				$html=$doc->saveHtml($node); 
				$metas = $doc->getElementsByTagName('ol'); 
				foreach($metas as $meta){
					if($meta->getAttribute('class')=='mb-15 reg searchCenterMiddle'){
						$getElement = $meta->getElementsByTagName('li'); 
						foreach($getElement as $element){
							$getTitle=$element->getElementsByTagName('h3'); 
							$getLink=$element->getElementsByTagName('a'); 
							$getDescription=$element->getElementsByTagName('div'); 
							
							if($getTitle->length>0 && $getLink->length>0 && $getDescription->length>0){
								$getLinkFull=$getLink->item(0)->getAttribute('href');
								$itemSearch['title']=$getTitle->item(0)->nodeValue; 
								$getDomainLink=preg_replace('/(.*?RU=)(.*?)(\/RK.*)/', '$2', urldecode($getLinkFull)); 
								if(!empty($getDomainLink)){
									$parsedUrl=parse_url($getDomainLink); 
									$domain=$this->_rulesDomain->resolve($parsedUrl['host']); 
									if(!empty($domain->getRegistrableDomain())){
										$itemSearch['linkFull']=$getDomainLink; 
										$itemSearch['domainRegister']=$domain->getRegistrableDomain(); 
									}
								}
								foreach($getDescription as $getDes){
									if($getDes->getAttribute('class')=='compText aAbs'){
										$description=$getDes->nodeValue;
										$itemSearch['description']=str_replace("\n", "", str_replace("\r", "", $description)); 
									}
								}
								$status='true'; 
								if(!empty($itemSearch['title'])){
									if(!AppHelper::instance()->checkBlacklistWord($itemSearch['title'])){
										$status='false';
									}
								}
								if(!empty($itemSearch['description']))
								{
									if(!AppHelper::instance()->checkBlacklistWord($itemSearch['description'])){
										$status='false';
									}
								}
								if(!empty($itemSearch['domainRegister']))
								{
									if($itemSearch['domainRegister']=='cungcap.net' || $itemSearch['domainRegister']=='crviet.com' || $itemSearch['domainRegister']=='cungcap.vn'){
										$status='false'; 
									}
								}
								if($status=='true'){
									array_push($listArray,$itemSearch);
								}
							}
						}
					}
				}
			}
			unset($client);
			return $listArray;
		}catch (\GuzzleHttp\Exception\ServerException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\BadResponseException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\ClientException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\ConnectException $e){
			return 'false'; 
		}
	}
	public function getSearchBingFrom()
    {
		//return 'false';
		$listArray=[]; 
		$itemSearch=[];
		try {
			$client = new Client([
				'headers' => [ 'Content-Type' => 'text/html' ], 
				'connect_timeout' => '5',
				'timeout' => '5'
			]);
			$response = $client->request('GET', $this->_url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			//$dataConvertUtf8 = iconv('UTF-8', 'UTF-8//IGNORE', $dataConvertUtf8);
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8);    
			$xpath = new \DOMXpath($doc); 
			foreach ($xpath->evaluate('//ol[@id="b_results"]') as $node) {
				$doc->saveHtml($node); 
				$metas = $doc->getElementsByTagName('li'); 
				for ($i = 0; $i < $metas->length; $i++)
				{
					$meta = $metas->item($i);
					if($meta->getAttribute('class') == 'b_algo'){
						$getTitle=$meta->getElementsByTagName('h2'); 
						$getLink=$meta->getElementsByTagName('a'); 
						if($getLink->length>0 && $getTitle->length>0){
							$getLinkFull=$getLink->item(0)->getAttribute('href');
							$itemSearch['title']=$getTitle->item(0)->nodeValue; 
							$itemSearch['linkFull']=$getLinkFull; 
							if($meta->getElementsByTagName('p')->length>0){
								$itemSearch['description']=str_replace("\n", "", str_replace("\r", "", $meta->getElementsByTagName('p')->item(0)->nodeValue));  
							}
							$parsedUrl=parse_url($getLinkFull); 
							$domain=$this->_rulesDomain->resolve($parsedUrl['host']); 
							if(!empty($domain->getRegistrableDomain())){
								$itemSearch['domainRegister']=$domain->getRegistrableDomain(); 
							}
							$status='true'; 
							if(!empty($itemSearch['title'])){
								if(!AppHelper::instance()->checkBlacklistWord($itemSearch['title'])){
									$status='false';
								}
							}
							if(!empty($itemSearch['description']))
							{
								if(!AppHelper::instance()->checkBlacklistWord($itemSearch['description'])){
									$status='false';
								}
							}
							if(!empty($itemSearch['domainRegister']))
							{
								if($itemSearch['domainRegister']=='cungcap.net' || $itemSearch['domainRegister']=='crviet.com' || $itemSearch['domainRegister']=='cungcap.vn'){
									$status='false'; 
								}
							}
							if($status=='true'){
								array_push($listArray,$itemSearch);
							}
						}
					}
				}
			}
			unset($client);
			return $listArray;
		}catch (\GuzzleHttp\Exception\ServerException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\BadResponseException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\ClientException $e){
			return 'false'; 
		}catch (\GuzzleHttp\Exception\ConnectException $e){
			return 'false'; 
		}
	}
	function utf8_converter($array)
	{
		array_walk_recursive($array, function(&$item, $key){
			if(!mb_detect_encoding($item, 'utf-8', true)){
				$item = utf8_encode($item);
			}
		});

		return $array;
	}
}