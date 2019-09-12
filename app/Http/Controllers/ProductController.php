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
class ProductController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function productShow(Request $request){
		if(!empty($this->_parame['id'])){
			$note=Note::find($this->_parame['id']); 
			if(!empty($note->id)){
				$paginate=10; 
				$page = $request->has('page') ? $request->query('page') : 1; 
				$newProduct = Cache::store('memcached')->remember('newProduct',1, function()
				{
					return DB::connection('mongodb')->collection('note')
					->select('_id','type','title','description','created_at','updated_at')
					->where('type','affiliate')
					->where('status','active')
					->orderBy('updated_at','desc')
					->limit(20)->get(); 
				}); 
				/*if(!empty($note->parent)){
					$noteParent=Note::find($note->parent); 
					$getNoteRelate = Cache::store('memcached')->remember('note_product_parent'.$note->parent,1, function() use($note)
					{
						return Note::where('type','affiliate')->where('parent',$note->parent)->where('status','=','active')
						->orderBy('updated_at','desc')
						->limit(10)
						->get(); 
					}); 
				}else if(!empty($note->category_slug)){
					$getNoteRelate = Cache::store('memcached')->remember('note_product_category_slug'.$note->category_slug,1, function() use($note)
					{
						return Note::where('type','affiliate')->where('category_slug',$note->category_slug)->where('status','=','active')
						->orderBy('updated_at','desc')
						->limit(10)
						->get(); 
					});  
				}else{
					$getNoteRelate=array(); 
				}*/
				$getNoteRelate=array(); 
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
					'newProduct'=>$newProduct, 
					'getNoteRelate'=>$getNoteRelate, 
					'searchProduct'=>$searchProduct
				); 
				return Theme::view('product.show', $data);
			}else{
				$keyword=str_replace('+', ' ', $this->_parame['id']); 
				$keyword=str_replace('-', ' ', $keyword); 
				$data=array(
					'keyword'=>$keyword
				); 
				return Theme::view('404', $data);
			}
		}
	}
	public function productList(Request $request){
		$page = $request->has('page') ? $request->query('page') : 1; 
		$getNote = Cache::store('memcached')->remember('productList'.$page,1, function()
		{
			return DB::connection('mongodb')->collection('note')
			->select('type','title','description','created_at','updated_at')
			->where('type','affiliate')
			->orderBy('updated_at','desc')
			->simplePaginate(50); 
		}); 
		$data=array(
			'getNote'=>$getNote,
		); 
		return Theme::view('product.list', $data);
	}
}