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
use Redirect; 
class SearchController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function searchType(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$paginate=10; 
		if($this->_parame['type']=='category'){
			$term = trim($request->q); 
			if (empty($term)) {
				return response()->json([]);
			}
			$offSet = ($page * $paginate) - $paginate;
			$tags=Note::searchByQuery([
				'bool'=>[
					'must'=>[
						'multi_match' => [
							'query' => $term,
							'fields' => ['title']
						]
					], 
					'filter'=>[
						'term'=>[
							'type'=>'category'
						]
					]
				]
			], null, null, $paginate, $offSet); 
			$formatted_tags = [];
			foreach ($tags as $tag) {
				$formatted_tags[] = ['id' => $tag['title'], 'text' => $tag['title']];
			}
			return response()->json($formatted_tags);
		}else if($this->_parame['type']=='all'){
			$term = trim($request->q); 
			if (empty($term)) {
				return response()->json([]);
			}
			$offSet = ($page * $paginate) - $paginate;
			$tags=Note::searchByQuery([
				'bool'=>[
					'must'=>[
						'multi_match' => [
							'query' => $term,
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
			$formatted_tags= [];
			foreach ($tags as $tag) {
				if($tag['type']=='domain'){
					$formatted_tags[] = ['id' => $tag['id'],'type' => $tag['type'], 'value' => $tag['domain']];
				}else{
					$formatted_tags[] = ['id' => $tag['id'],'type' => $tag['type'], 'value' => $tag['title']];
				}
			}
			$suggestions['suggestions']= $formatted_tags;
			return response()->json($suggestions);
		}else if($this->_parame['type']=='get'){
			$q=trim($request->v); 
			$t=$request->t; 
			$i=$request->i; 
			if(!empty($i) && !empty($t)){
				if($t=='category'){
					return Redirect::to(route('show.category',array(config('app.url'),str_replace(' ','+',$q)))); 
				}else if($t=='video'){
					return Redirect::to(route('video.show',array(config('app.url'),$i,str_slug($q,'-')))); 
				}else if($t=='post'){
					return Redirect::to(route('post.show',array(config('app.url'),$i,str_slug($q,'-')))); 
				}else if($t=='channel'){
					return Redirect::to(route('profile',array(config('app.url'),$i))); 
				}else if($t=='affiliate'){
					return Redirect::to(route('product.show',array(config('app.url'),$i,str_slug($q, '-')))); 
				}else if($t=='company'){
					return Redirect::to(route('company.show',array(config('app.url'),$i,str_slug($q,'-')))); 
				}else if($t=='news'){
					return Redirect::to(route('news.show',array(config('app.url'),$i,str_slug($q,'-')))); 
				}else if($t=='site'){
					return Redirect::to(route('site.show',array(config('app.url'),$i,str_slug($q,'-')))); 
				}
			}else{
				$offSet = ($page * $paginate) - $paginate;
				$noteSearch=Note::searchByQuery([
					'bool'=>[
						'must'=>[
							'multi_match' => [
								'query' => $q,
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
				$querystringArray = ['v' => $q,'i'=>$i,'t'=>$t];
				$noteSearch->appends($querystringArray)->render(); 
				$categorySearch=Note::searchByQuery([
					'bool'=>[
						'must'=>[
							'multi_match' => [
								'query' => $q,
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
				$data=array(
					'searchProduct'=>$noteSearch, 
					'categorySearch'=>$categorySearch, 
					'keyword'=>$q, 
					'total'=>$total
				); 
				return Theme::view('search.show', $data);
			}
		}
	}
}