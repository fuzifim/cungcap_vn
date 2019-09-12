<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Http\Request; 
use Theme; 
use App\Model\Note; 
use App\Model\Jobs; 
use App\User; 
use Cache; 
use Carbon\Carbon; 
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Client; 
use AppHelper; 
use Redirect; 
use DB; 
class IndexController extends ConstructController
{
	public $_domain; 
	public $_keyword; 
	public $_searchResult=array(); 
	public $_url; 
	public $_dataCraw; 
	public function __construct(){
		parent::__construct(); 
	}
	public function craw(){
		$this->_keyword=str_replace('+', ' ', $this->_parame['keyword']); 
		$searchResult=$this->getCraw(); 
		if($searchResult['data']!='false'){
			return response()->json(['success'=>true,
				'result'=>$searchResult, 
			]);
		}else{
			return response()->json(['success'=>false,
				'result'=>$searchResult['from'], 
			]);
		}
	}
	public function index(Request $request){
		$parsedUrl=parse_url($request->url()); 
		$this->_domainName = $this->_rulesDomain->resolve($parsedUrl['host']); 
		if(!empty($this->_domainName->getSubDomain()) && $this->_domainName->getSubDomain()!='www'){
			$this->_pieces = explode("-", $this->_domainName->getSubDomain()); 
			$checkWWW = explode(".", $this->_domainName->getSubDomain()); 
			if(!empty($this->_pieces[0]) && $this->_pieces[0]=='post'){
				//$this->_siteSuccess='infoPost'; 
			}else if(!empty($this->_pieces[0]) && $this->_pieces[0]=='news'){
				return Redirect::to('https://'.config('app.url').'/news/'.$this->_pieces[1].'/old',301); 
			}else if(!empty($this->_pieces[0]) && $this->_pieces[0]=='feed'){ 
				//$this->_siteSuccess='infoFeed'; 
			}else if(!empty($this->_pieces[0]) && $this->_pieces[0]=='com'){
				return Redirect::to('https://'.config('app.url').'/com/'.$this->_pieces[1].'/old',301); 
			}else if(!empty($checkWWW[0]) && $checkWWW[0]=='www'){
				$url=str_replace('www.','',$request->url()); 
				return Redirect::to($url,301); 
			}else{
				$fixDomain = substr($this->_domainName->getSubDomain(), 0, -2);
				$this->_domain=$fixDomain; 
				return $this->domainShow(); 
			}
		}
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNewNote = Cache::store('memcached')->remember('newNoteUpdate'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('type','title','description','created_at','updated_at')
			->where('type','category')
			->where('status','active')
			//->orderBy('created_at','asc')
			->simplePaginate(10); 
		}); 
		$notePostNew = Cache::store('memcached')->remember('note_post_home'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('_id','type','title','content','media','view','created_at','updated_at')
			->where('type','post')
			->where('status','active')
			->orderBy('updated_at','desc')
			->simplePaginate(12); 
		}); 
		$data=array(
			'getNote'=>$getNewNote, 
			'postNew'=>$notePostNew
		); 
		return Theme::view('index', $data);
	}
	public function categoryList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('categoryList_'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('type','title','description','image','thumb','created_at','updated_at')
			->where('type','category')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('category.list', $data);
	}
	public function showCategory(Request $request){
		if(!empty($this->_parame['slug'])){
			$paginate=10; 
			$page = $request->has('page') ? $request->query('page') : 1; 
			$title=str_replace('+', ' ', $this->_parame['slug']); 
			$note=Note::where('type','category')->where('title_encode',base64_encode($title))->first(); 
			if(!empty($note->id)){
				$note->increment('view',1); 
				$offSet = ($page * $paginate) - $paginate;
				$noteSearch=Note::searchByQuery([
					'bool'=>[
						'must'=>[
							'multi_match' => [
								'query' => $note->title,
								'fields' => ['title','tags']
							]
						], 
						'filter'=>[
							'terms'=>[
								'type'=>['post','video','channel','affiliate','company','news','site']
							]
						]
					]
				], null, null, $paginate, $offSet); 
				$itemsForCurrentPage = $noteSearch->toArray();  
				$total=$noteSearch->totalHits(); 
				$noteSearch = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, $noteSearch->totalHits()/$paginate, $paginate, $page);
				$noteSearch->setPath($request->url()); 
				//$querystringArray = ['v' => $q,'i'=>$i,'t'=>$t];
				//$noteSearch->appends($querystringArray)->render(); 
				$categorySearch=Note::searchByQuery([
					'bool'=>[
						'must'=>[
							'multi_match' => [
								'query' => $note->title,
								'fields' => ['title','tags']
							]
						], 
						'filter'=>[
							'terms'=>[
								'type'=>['category']
							]
						]
					]
				], null, null, $paginate, $offSet); 
				$getNote = Cache::store('memcached')->remember('getNote_id_'.$note->id.'_page_'.$page,1, function() use($note)
				{
					return DB::connection('mongodb')->collection('note')
					->select('_id','type','domain','title','image','thumb','description','link','attribute','created_at','updated_at')
					->where('parent',$note->id)
					//->orderBy('updated_at','desc')
					->simplePaginate(50); 
				});
				$data=array(
					'note'=>$note, 
					'getNote'=>$getNote, 
					'searchProduct'=>$noteSearch, 
					'categorySearch'=>$categorySearch, 
					'keyword'=>$note->title, 
					'total'=>$total, 
					'type'=>'category'
				); 
				return Theme::view('search.show', $data);
			}else{
				$keyword=str_replace('+', ' ', $this->_parame['slug']); 
				$keyword=str_replace('-', ' ', $keyword); 
				$data=array(
					'keyword'=>$keyword
				); 
				return Theme::view('404', $data);
				//return redirect('https://'.config('app.url'));
				//abort(404);
			}
		}
	}
	public function goTo(){
		if(!empty($this->_parame['id'])){
			$note=Note::find($this->_parame['id']); 
			if(!empty($note->id)){
				$data=array(
					'note'=>$note
				); 
				return Theme::view('goto', $data);
			}
		}
	} 
	public function videoList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('videoList_'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('type','title','description','image','thumb','created_at','updated_at')
			->where('type','video')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('video.list', $data);
	}
	public function videoShow(Request $request){
		if(!empty($this->_parame['id'])){
			$note=Note::find($this->_parame['id']); 
			if(!empty($note->id) && $note->type=='video'){
				$note->increment('view',1); 
				$paginate=10; 
				$page = $request->has('page') ? $request->query('page') : 1; 
				if(!empty($note->parent)){
					$noteParent=Note::find($note->parent); 
					$getNoteRelate = Cache::store('memcached')->remember('note_parent'.$note->parent,1, function() use($note)
					{
						return Note::where('parent',$note->parent)->where('status','=','active')
						->orderBy('updated_at','desc')
						->get(); 
					}); 
				}else{
					$noteParent=array(); 
					$getNoteRelate=array(); 
				}
				$searchProduct = Cache::store('memcached')->remember('search_product_'.$note->id.'_page_'.$page,1, function() use($note,$paginate,$page,$request)
				{
					$offSet = ($page * $paginate) - $paginate;
					$searchProduct=Note::searchByQuery([
						'bool'=>[
							'must'=>[
								'multi_match' => [
									'query' => $note->title,
									'fields' => ['title']
								]
							], 
							'filter'=>[
								'term'=>[
									'type'=>'affiliate'
								]
							]
						]
					], null, null, $paginate, $offSet); 
					$itemsForCurrentPage = $searchProduct->toArray();  
					$searchProduct = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, $searchProduct->totalHits()/$paginate, $paginate, $page);
					$searchProduct->setPath($request->url()); 
					return $searchProduct; 
				});
				$data=array(
					'note'=>$note, 
					'getNoteRelate'=>$getNoteRelate, 
					'noteParent'=>$noteParent, 
					'searchProduct'=>$searchProduct
				); 
				return Theme::view('video.show', $data);
			}
		}
	}
	public function siteList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('siteList_'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('type','title','description','image','thumb','created_at','updated_at')
			->where('type','site')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('site.list', $data);
	}
	public function siteShow(Request $request){
		if(!empty($this->_parame['id'])){
			$note=Note::find($this->_parame['id']); 
			if(!empty($note->id) && $note->type=='site'){
				$note->increment('view',1); 
				$paginate=10; 
				$page = $request->has('page') ? $request->query('page') : 1; 
				if(!empty($note->parent)){
					$noteParent=Note::find($note->parent); 
					$getNoteRelate = Cache::store('memcached')->remember('note_parent'.$note->parent,1, function() use($note)
					{
						return Note::where('parent',$note->parent)->where('status','=','active')
						->orderBy('updated_at','desc')
						->get(); 
					}); 
				}else{
					$getNoteRelate=array(); 
					$noteParent=array(); 
				}
				$searchProduct = Cache::store('memcached')->remember('search_product_'.$note->id.'_page_'.$page,1, function() use($note,$paginate,$page,$request)
				{
					$offSet = ($page * $paginate) - $paginate;
					$searchProduct=Note::searchByQuery([
						'bool'=>[
							'must'=>[
								'multi_match' => [
									'query' => $note->title,
									'fields' => ['title']
								]
							], 
							'filter'=>[
								'term'=>[
									'type'=>'affiliate'
								]
							]
						]
					], null, null, $paginate, $offSet); 
					$itemsForCurrentPage = $searchProduct->toArray();  
					$searchProduct = new \Illuminate\Pagination\LengthAwarePaginator($itemsForCurrentPage, $searchProduct->totalHits()/$paginate, $paginate, $page);
					$searchProduct->setPath($request->url()); 
					return $searchProduct; 
				});
				$data=array(
					'note'=>$note, 
					'getNoteRelate'=>$getNoteRelate, 
					'noteParent'=>$noteParent, 
					'searchProduct'=>$searchProduct
				); 
				return Theme::view('site.show', $data);
			}
		}
	}
	public function postShow(Request $request){
		if(!empty($this->_parame['id'])){
			$page = $request->has('page') ? $request->query('page') : 1; 
			$note=Note::find($this->_parame['id']); 
			if(!empty($note->id) && $note->type=='post'){
				$note->increment('view',1); 
				if($note->status=='active'){
					if(!empty($note->parent)){
						$noteParent=Note::find($note->parent); 
						$getNoteRelate = Cache::store('memcached')->remember('note_parent'.$note->parent,1, function() use($note)
						{
							return Note::where('parent',$note->parent)->where('_id','!=',$note->id)->where('status','=','active')->orderBy('created_at','asc')->get(); 
						}); 
					}else{
						$getNoteRelate = Cache::store('memcached')->remember('note_parent_user'.$note->user_id,1, function() use($note)
						{
							return DB::connection('mongodb')->collection('note')
							->select('_id','type','title','content','media','view','created_at','updated_at')
							->where('type','post')
							->where('_id','!=',$note->id)
							->where('user_id',$note->user_id)
							->where('status','active')
							->orderBy('updated_at','desc')
							->limit(9)->get(); 
						}); 
						$noteParent=array(); 
					}
					$notePostNew = Cache::store('memcached')->remember('note_post_new'.$page,1, function() use($note)
					{
						return DB::connection('mongodb')->collection('note')
						->select('_id','type','title','content','media','view','created_at','updated_at')
						->where('type','post')
						->where('_id','!=',$note->id)
						->where('status','active')
						->orderBy('updated_at','desc')
						->simplePaginate(5); 
					}); 
					$data=array(
						'note'=>$note, 
						'getNoteRelate'=>$getNoteRelate, 
						'noteParent'=>$noteParent, 
						'notePostNew'=>$notePostNew
					); 
					return Theme::view('post.show', $data);
				}else{
					$data=array(
						'keyword'=>$note->title
					); 
					return Theme::view('404', $data);
				}
			}
		}
	}
	public function domainList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('domainList_'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('type','domain','title','description','created_at','updated_at')
			->where('type','domain')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('domain.list', $data);
	}
	public function domainShow(){
		if(!empty($this->_domain)){
			$note=Note::where('type','domain')->where('domain_encode',base64_encode($this->_domain))->first(); 
			if(!empty($note->id)){
				$note->increment('view',1); 
				$this->_domain=$note->domain; 
				if(!empty($note->parent)){
					$noteParent=Note::find($note->parent); 
				}else{
					$noteParent=array(); 
				}
				$getNoteRelate = Cache::store('memcached')->remember('domain_new_20',1, function()
				{
					return DB::connection('mongodb')->collection('note')
					->select('_id','type','title','description','domain','created_at','updated_at')
					->where('type','domain')
					->where('status','active')
					->orderBy('updated_at','desc')
					->limit(20)->get(); 
				}); 
				$siteRelate=array(); 
				$siteRelateItem=array(); 
				if(!empty($note->attribute['site_related']) && count($note->attribute['site_related'])>0){
					$siteRelate = Cache::store('memcached')->remember('siteRelate_'.$note->id,1, function() use ($note)
					{
						return DB::connection('mongodb')->collection('note')
						->select('_id','type','title','description','attribute','link','created_at','updated_at')
						->where('type','site')
						->whereIn('_id',$note->attribute['site_related'])
						//->where('status','active')
						->orderBy('updated_at','desc')
						->get(); 
					}); 
				}
				$newDomain = array(); 
				$data = Cache::store('memcached')->remember('data_'.$note->id,1, function() use($note,$getNoteRelate,$noteParent,$newDomain,$siteRelate)
				{
					return array(
						'note'=>$note, 
						'getNoteRelate'=>$getNoteRelate, 
						'noteParent'=>$noteParent, 
						'newDomain'=>$newDomain, 
						'siteRelate'=>$siteRelate
					);  
				}); 
				return Theme::view('domain.show', $data);
			}else{
				$data=array(
					'keyword'=>$this->_domain
				); 
				return Theme::view('404', $data);
			}
		}
	}
	public function domainShowOld(){
		
	}
	public function companyShow(){
		if(!empty($this->_parame['id'])){
			$note=Note::find($this->_parame['id']); 
			if(empty($note->id)){
				$note=Note::where('type','company')->where('old_id',(int)$this->_parame['id'])->first(); 
			}
			if(!empty($note->id)){
				$note->increment('view',1); 
				if(!empty($note->parent)){
					$noteParent=Note::find($note->parent); 
					$getNoteRelate = Cache::store('memcached')->remember('note_parent'.$note->parent,1, function() use($note)
					{
						return Note::where('parent',$note->parent)->where('status','=','active')->orderBy('updated_at','desc')->limit(20)->get(); 
					}); 
				}else{
					$noteParent=array(); 
					$getNoteRelate=array(); 
				}
				$newCompany = Cache::store('memcached')->remember('newCompany',1, function()
				{
					return DB::connection('mongodb')->collection('note')
					->select('_id','type','title','description','created_at','updated_at')
					->where('type','company')
					->orderBy('updated_at','desc')
					->limit(20)->get(); 
				}); 
				$data=array(
					'note'=>$note, 
					'getNoteRelate'=>$getNoteRelate, 
					'noteParent'=>$noteParent, 
					'newCompany'=>$newCompany,
				); 
				return Theme::view('company.show', $data);
			}
		}
	}
	public function companyList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('companyList_page_'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('_id','type','title','description','created_at','updated_at')
			->where('type','company')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		//dd(DB::connection('mongodb')->collection('note')->where('type','company')->orderBy('updated_at','desc')->limit(12)->get()); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('company.list', $data);
	}
	public function newsShow(){
		//dd(Note::where('type','company')->first()); 
		if(!empty($this->_parame['id'])){
			$note=Note::find($this->_parame['id']); 
			if(empty($note->id)){
				$note=Note::where('type','news')->where('old_id',(int)$this->_parame['id'])->first(); 
			}
			$getNoteNews = Cache::store('memcached')->remember('note_news',1, function()
			{
				return Note::where('type','news')
				->orderBy('created_at','desc')
				->limit(20)->get(); 
			}); 
			if(!empty($note->id)){
				if(!empty($note->parent)){
					$noteParent=Note::find($note->parent); 
					$getNoteRelate = Cache::store('memcached')->remember('note_parent'.$note->parent,1, function() use($note)
					{
						return Note::where('parent',$note->parent)->where('status','=','active')
						->orderBy('updated_at','desc')
						->limit(20)->get(); 
					}); 
				}else{
					$noteParent=array(); 
					$getNoteRelate=array(); 
				}
				$data=array(
					'note'=>$note, 
					'getNoteRelate'=>$getNoteRelate, 
					'noteParent'=>$noteParent, 
					'getNoteNews'=>$getNoteNews
				); 
				return Theme::view('news.show', $data);
			}
		}
	}
	public function newsList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('newsList_page_'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('_id','type','title','description','created_at','updated_at')
			->where('type','news')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('news.list', $data);
	}
	public function sitemap(){
		//return false;  
		if($this->_parame['type']=='_category.xml'){
			$sitemapIndex='false'; 
			$getNote = Cache::store('memcached')->remember('sitemap_category',1, function()
			{
				return DB::connection('mongodb')->collection('note')->select('type','title', 'created_at')->where('type','category')->where('status','active')
				->orderBy('updated_at','desc')
				->limit(1000)->get(); 
			}); 
		}else if($this->_parame['type']=='_video.xml'){
			$sitemapIndex='false'; 
			$getNote = Cache::store('memcached')->remember('sitemap_video',1, function()
			{
				return DB::connection('mongodb')->collection('note')->select('_id','type','title', 'created_at')->where('type','video')
				->orderBy('updated_at','desc')
				->limit(1000)->get(); 
			}); 
		}else if($this->_parame['type']=='_domain.xml'){
			$sitemapIndex='false'; 
			$getNote = Cache::store('memcached')->remember('sitemap_domain',1, function()
			{
				return DB::connection('mongodb')->collection('note')->select('type','domain', 'created_at','updated_at')
				->where('type','domain')
				->where('status','active')
				->orderBy('updated_at','desc')
				->limit(1000)->get(); 
			});  
		}else if($this->_parame['type']=='_news.xml'){
			$sitemapIndex='false'; 
			$getNote = Cache::store('memcached')->remember('sitemap_news',1, function()
			{
				return DB::connection('mongodb')->collection('note')->select('_id','type','title', 'created_at')->where('type','news')
				->orderBy('updated_at','desc')
				->limit(1000)->get(); 
			}); 
		}else if($this->_parame['type']=='_company.xml'){
			$sitemapIndex='false'; 
			$getNote = Cache::store('memcached')->remember('sitemap_company',1, function()
			{
				//return Note::where('type','company')->where('status','active')->orderBy('created_at','desc')->limit(1000)->get(); 
				return DB::connection('mongodb')->collection('note')->select('_id','type','title', 'created_at')->where('type','company')
				->orderBy('updated_at','desc')
				->limit(1000)->get(); 
			}); 
		}else if($this->_parame['type']=='_product.xml'){
			$sitemapIndex='false'; 
			$getNote = Cache::store('memcached')->remember('sitemap_product',1, function()
			{
				return DB::connection('mongodb')->collection('note')->select('_id','type','title', 'created_at')->where('type','affiliate')
				->orderBy('updated_at','desc')
				->limit(1000)->get(); 
			}); 
		}else{
			$sitemapIndex='true'; 
			$getNote=array(); 
		}
		$data=array(
			'sitemapIndex'=>$sitemapIndex, 
			'getNote'=>$getNote,
		); 
		return response()->view('sitemap',$data)->header('Content-Type', 'text/xml');
	}
	public function rss(){
		//return false; 
		if($this->_parame['type']=='_video'){
			$sitemapIndex='false'; 
			$getNote = Cache::store('memcached')->remember('rss_video_new',1, function()
			{
				return DB::connection('mongodb')->collection('note')->select('_id','type','title','description','image', 'created_at')->where('type','video')->where('status','active')
				->orderBy('updated_at','desc')
				->limit(20)->get(); 
			}); 
		}else if($this->_parame['type']=='_category'){
			$sitemapIndex='false'; 
			$getNote = Cache::store('memcached')->remember('rss_category_new',1, function()
			{
				return DB::connection('mongodb')->collection('note')->select('_id','type','title','description','image', 'created_at')->where('type','category')->where('status','active')
				->orderBy('updated_at','desc')
				->limit(20)->get(); 
			}); 
		}
		$data=array(
			'sitemapIndex'=>$sitemapIndex, 
			'getNote'=>$getNote,
		); 
		return response()->view('rss',$data)->header('Content-Type', 'text/xml');
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
		$urlRank='http://data.alexa.com/data?cli=10&url='.$this->_domain; 
		$getXml=simplexml_load_file($urlRank); 
		if(!empty($getXml->SD->POPULARITY['TEXT'])){
			$rank=$getXml->SD->POPULARITY['TEXT']; 
		}
		if(!empty($getXml->SD->COUNTRY['CODE'])){
			$country_code=$getXml->SD->COUNTRY['CODE']; 
		}
		if(!empty($getXml->SD->COUNTRY['RANK'])){
			$rank_country=$getXml->SD->COUNTRY['RANK']; 
		}
		try {
			$client = new Client([
				'headers' => [ 
					'Content-Type' => 'text/html',
					'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36'
				],  
				'connect_timeout' => '5',
				'timeout' => '5'
			]);
			$response = $client->request('GET', 'http://'.$this->_domain); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			$dataConvertUtf8 = iconv('UTF-8', 'UTF-8//IGNORE', $dataConvertUtf8);
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
	public function getCraw(){
		$result=[]; 
		$getJob=Jobs::where('type','craw_search_from_ip')->first(); 
		if(empty($getJob->id)){
			$data=array(
				'type'=>'craw_search_from_ip',
				'value'=>'google', 
				'parent'=>0, 
				'updated_at'=>Carbon::now()->format('Y-m-d H:i:s'), 
			); 
			$getJob=Jobs::where('type','craw_search_from_ip')->update($data, ['upsert' => true]); 
		}
		if($getJob->value=='google'){
			$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
			$getJob->value='yahoo';
			$getJob->save(); 
			$searchResult=$this->getSearchGoogle(); 
			if($searchResult!='false' && count($searchResult)>0){
				$result['from']='google';
				$result['data']=$searchResult; 
				return $result; 
			}
		}else if($getJob->value=='yahoo'){
			$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
			$getJob->value='bing';
			$getJob->save(); 
			$searchResult=$this->getSearchYahoo(); 
			if($searchResult!='false' && count($searchResult)>0){
				$result['from']='yahoo';
				$result['data']=$searchResult; 
				return $result;
			}
		}else if($getJob->value=='bing'){
			$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
			$getJob->value='aol';
			$getJob->save(); 
			$searchResult=$this->getSearchBing(); 
			if($searchResult!='false' && count($searchResult)>0){
				$result['from']='bing';
				$result['data']=$searchResult; 
				return $result;
			}
		}else if($getJob->value=='aol'){
			$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
			$getJob->value='dogpile';
			$getJob->save(); 
			$searchResult=$this->getSearchAol(); 
			if($searchResult!='false' && count($searchResult)>0){
				$result['from']='aol';
				$result['data']=$searchResult; 
				return $result;
			}
		}else if($getJob->value=='dogpile'){
			$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
			$getJob->value='metacrawler';
			$getJob->save(); 
			$searchResult=$this->getSearchDogpile(); 
			if($searchResult!='false' && count($searchResult)>0){
				$result['from']='dogpile';
				$result['data']=$searchResult; 
				return $result;
			}
		}else if($getJob->value=='metacrawler'){
			$getJob->updated_at=Carbon::now()->format('Y-m-d H:i:s'); 
			$getJob->value='google';
			$getJob->save(); 
			$searchResult=$this->getSearchMetacrawler(); 
			if($searchResult!='false' && count($searchResult)>0){
				$result['from']='metacrawler';
				$result['data']=$searchResult; 
				return $result;
			}
		}
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
			$dataConvertUtf8 = iconv('UTF-8', 'UTF-8//IGNORE', $dataConvertUtf8);
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
			$dataConvertUtf8 = iconv('UTF-8', 'UTF-8//IGNORE', $dataConvertUtf8);
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
			$dataConvertUtf8 = iconv('UTF-8', 'UTF-8//IGNORE', $dataConvertUtf8);
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
	public function getSearchGoogle()
    {
		$listArray=[]; 
		$itemSearch=[]; 
		try {
			$client = new Client([
				'headers' => [ 'Content-Type' => 'text/html' ], 
				'connect_timeout' => '2',
				'timeout' => '2'
			]);
			$url='https://www.google.com.vn/search?q='.urlencode($this->_keyword); 
			$response = $client->request('GET', $url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
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
									$itemSearch['description']=$description; 
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
							if(!empty($itemSearch['domainRegister']) && $itemSearch['domainRegister']=='cungcap.net' && $itemSearch['domainRegister']=='crviet.com' && $itemSearch['domainRegister']=='cungcap.vn')
							{
								$status='false'; 
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
	public function getSearchYahoo()
    {
		//return 'false';
		$listArray=[]; 
		$itemSearch=[];
		try {
			$client = new Client([
				'headers' => [ 
					'Content-Type' => 'text/html',
					'User-Agent' => 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n'
				],
				'connect_timeout' => '2',
				'timeout' => '2'
			]);
			$url='https://vn.search.yahoo.com/search?p='.urlencode($this->_keyword); 
			$response = $client->request('GET', $url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8);    
			$xpath = new \DOMXpath($doc);  
			foreach ($xpath->evaluate('//div[@id="web"]') as $node) {
				$html=$doc->saveHtml($node); 
				$metas = $doc->getElementsByTagName('ol'); 
				foreach($metas as $meta){
					if($meta->getAttribute('class')=='reg searchCenterMiddle'){
						$getElement = $meta->getElementsByTagName('li'); 
						foreach($getElement as $element){
							$getTitle=$element->getElementsByTagName('h3'); 
							$getLink=$element->getElementsByTagName('a'); 
							$getDescription=$element->getElementsByTagName('p'); 
							
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
									if($getDes->getAttribute('class')=='mw-42em'){
										$description=$getDes->nodeValue;
										$itemSearch['description']=$description; 
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
								if(!empty($itemSearch['domainRegister']) && $itemSearch['domainRegister']=='cungcap.net' && $itemSearch['domainRegister']=='crviet.com' && $itemSearch['domainRegister']=='cungcap.vn')
								{
									$status='false'; 
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
	public function getSearchBing()
    {
		//return 'false';
		$listArray=[]; 
		$itemSearch=[];
		try {
			$client = new Client([
				'headers' => [ 'Content-Type' => 'text/html' ], 
				'connect_timeout' => '2',
				'timeout' => '2'
			]);
			$url='https://www.bing.com/search?q='.urlencode($this->_keyword); 
			$response = $client->request('GET', $url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
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
								$itemSearch['description']=$meta->getElementsByTagName('p')->item(0)->nodeValue; 
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
							if(!empty($itemSearch['domainRegister']) && $itemSearch['domainRegister']=='cungcap.net' && $itemSearch['domainRegister']=='crviet.com' && $itemSearch['domainRegister']=='cungcap.vn')
							{
								$status='false'; 
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
	public function getSearchAol()
    {
		//return 'false';
		$listArray=[]; 
		$itemSearch=[];
		try {
			$client = new Client([
				'headers' => [ 
					'Content-Type' => 'text/html',
					'User-Agent' => 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n'
				],
				'connect_timeout' => '2',
				'timeout' => '2'
			]);
			$url='https://search.aol.com/aol/search?q='.urlencode($this->_keyword);  
			$response = $client->request('GET', $url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8);    
			$xpath = new \DOMXpath($doc); 
			foreach ($xpath->evaluate('//div[@id="web"]') as $node) {
				$doc->saveHtml($node); 
				$metas = $doc->getElementsByTagName('ol');
				if($metas->length>2){
					$getElement=$metas->item(2)->getElementsByTagName('li'); 
					if($getElement->length>0){
						foreach($getElement as $element){
							//dd($element->nodeValue); 
							$getTitle=$element->getElementsByTagName('h3'); 
							$getLink=$element->getElementsByTagName('a'); 
							$getDescription=$element->getElementsByTagName('p'); 
							if($getTitle->length>0 && $getDescription->length>0){
								$getLinkFull=$getLink->item(0)->getAttribute('href');
								$itemSearch['title']=$getTitle->item(0)->nodeValue; 
								$itemSearch['description']=$getDescription->item(0)->nodeValue; 
								$getDomainLink=preg_replace('/(.*?RU=)(.*?)(\/RK.*)/', '$2', urldecode($getLinkFull)); 
								if(!empty($getDomainLink)){
									$parsedUrl=parse_url($getDomainLink); 
									$domain=$this->_rulesDomain->resolve($parsedUrl['host']); 
									if(!empty($domain->getRegistrableDomain())){
										$itemSearch['linkFull']=$getDomainLink; 
										$itemSearch['domainRegister']=$domain->getRegistrableDomain(); 
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
								if(!empty($itemSearch['domainRegister']) && $itemSearch['domainRegister']=='cungcap.net' && $itemSearch['domainRegister']=='crviet.com' && $itemSearch['domainRegister']=='cungcap.vn')
								{
									$status='false'; 
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
	public function getSearchDogpile()
    {
		//return 'false';
		$listArray=[]; 
		$itemSearch=[]; 
		try {
			$client = new Client([
				'headers' => [ 
					'Content-Type' => 'text/html',
					'User-Agent' => 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n'
				],
				'connect_timeout' => '2',
				'timeout' => '2'
			]);
			$url='http://results.dogpile.com/search/web?q='.urlencode($this->_keyword); 
			$response = $client->request('GET', $url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8);     
			$xpath = new \DOMXpath($doc); 
			$domainRegister='';
			foreach ($xpath->evaluate('//div[@class="main web"]') as $node) {
				$html=$doc->saveHtml($node); 
				$metas = $doc->getElementsByTagName('div'); 
				foreach($metas as $meta){
					if($meta->getAttribute('class')=='results web'){
						$params1 = $meta->getElementsByTagName('div'); 
						foreach($params1 as $para){
							if($para->getAttribute('class')=='result'){
								$getTitle=$para->getElementsByTagName('a'); 
								$getDescription=$para->getElementsByTagName('span'); 
								if($getTitle->length>0 && $getDescription->length>1){
									$getLinkFull=$getTitle->item(0)->getAttribute('href');
									$itemSearch['title']=$getTitle->item(0)->nodeValue; 
									$itemSearch['linkFull']=$getLinkFull; 
									$description=$getDescription->item(1)->nodeValue;
									$itemSearch['description']=$description; 
									if(!empty($getLinkFull)){
										$parsedUrl=parse_url($getLinkFull); 
										$domain=$this->_rulesDomain->resolve($parsedUrl['host']); 
										if(!empty($domain->getRegistrableDomain())){
											$itemSearch['domainRegister']=$domain->getRegistrableDomain(); 
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
									if(!empty($itemSearch['domainRegister']) && $itemSearch['domainRegister']=='cungcap.net' && $itemSearch['domainRegister']=='crviet.com' && $itemSearch['domainRegister']=='cungcap.vn')
									{
										$status='false'; 
									}
									if($status=='true'){
										array_push($listArray,$itemSearch);
									}
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
	public function getSearchMetacrawler()
    {
		//return 'false';
		$listArray=[]; 
		$itemSearch=[]; 
		try {
			$client = new Client([
				'headers' => [ 
					'Content-Type' => 'text/html',
					'User-Agent' => 'Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n'
				],
				'connect_timeout' => '2',
				'timeout' => '2'
			]);
			$url='http://www.metacrawler.com/search/web?q='.urlencode($this->_keyword); 
			$response = $client->request('GET', $url); 
			$getResponse=$response->getBody()->getContents(); 
			$dataConvertUtf8 = '<?xml version="1.0" encoding="UTF-8"?>'.$getResponse;
			$doc = new \DOMDocument;
			@$doc->loadHTML($dataConvertUtf8);    
			$xpath = new \DOMXpath($doc); 
			foreach ($xpath->evaluate('//div[@id="resultsMain"]') as $node) {
				$doc->saveHtml($node); 
				$metas = $doc->getElementsByTagName('div');
				foreach($metas as $meta){
					if($meta->getAttribute('class')=='searchResult webResult'){
						$getElement=$meta->getElementsByTagName('div'); 
						foreach($getElement as $element){
							if($element->getAttribute('class')=='resultTitlePane'){
								$getLink=$element->getElementsByTagName('a'); 
								$getLinkFull=$getLink->item(0)->getAttribute('href');
								$itemSearch['title']=$getLink->item(0)->nodeValue; 
								$getDomainLink=preg_replace('/(.*?ru=)(.*?)(&du.*)/', '$2', urldecode(urldecode($getLinkFull))); 
								if(!empty($getDomainLink)){
									$parsedUrl=parse_url($getDomainLink); 
									$domain=$this->_rulesDomain->resolve($parsedUrl['host']); 
									if(!empty($domain->getRegistrableDomain())){
										$itemSearch['linkFull']=$getDomainLink; 
										$itemSearch['domainRegister']=$domain->getRegistrableDomain(); 
									}
								}
							}
							if($element->getAttribute('class')=='resultDescription'){
								$itemSearch['description']=$element->nodeValue; 
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
						if(!empty($itemSearch['domainRegister']) && $itemSearch['domainRegister']=='cungcap.net' && $itemSearch['domainRegister']=='crviet.com' && $itemSearch['domainRegister']=='cungcap.vn')
							{
								$status='false'; 
							}
						if($status=='true'){
							array_push($listArray,$itemSearch);
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
}