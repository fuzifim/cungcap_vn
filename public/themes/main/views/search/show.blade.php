<?

Theme::setSearch($keyword); 
Theme::setType('website'); 
if(AppHelper::instance()->checkBlacklistWord($keyword) && count($searchProduct)>0){
	$ads='true';
}else{
	$ads='false';
}
if(!empty($type) && $type=='category'){
	if(AppHelper::instance()->checkWordCC($note->title)){
		Theme::setTitle(config('app.name').' '.htmlspecialchars($note->title)); 
	}else{
		Theme::setTitle(htmlspecialchars($note->title)); 
	}
	$noteImage=''; 
	if(!empty($note['image'])){ 
		$noteImage=$note['image']; 
	}else if(count($getNote)){
		$i=0;
		foreach($getNote as $item){
			if($item['type']=='image'){
				$i++; 
				if($i==1){ 
					$noteImage='https:'.$item['attribute']['image']; 
				}
			}
		}
		if(!empty($noteImage)){
			$note->image=$noteImage; 
			$note->save(); 
		}
	}
	Theme::setImage($noteImage); 
	Theme::setCanonical(route('show.category',array(config('app.url'),str_replace(' ','+',$note->title)))); 
}else{
	Theme::setTitle($keyword); 
}
?>
@partial('header') 
@if(!empty($type) && $type=='category' && count($getNote))
	<? $k=0; $textDescription='';?>
	@foreach($getNote as $item)
		@if($item['type']=='site')
			<? $k++; ?>
			@if($k==1 && !Theme::has('description'))
				<? $textDescription.=(str_replace("\n", "", str_replace("\r", "", $item['title'])));?> 
			@endif 
			@if($k==2 && !Theme::has('description'))
				<? $textDescription.=', '.(str_replace("\n", "", str_replace("\r", "", $item['title'])));?> 
			@endif 
			@if($k==3 && !Theme::has('description'))
				<? $textDescription.=', '.(str_replace("\n", "", str_replace("\r", "", $item['title'])));?> 
			@endif 
		@endif
	@endforeach 
	@if(!empty($note->description))
		<?Theme::setDescription($note->description);?> 
	@elseif(!empty($textDescription))
		<?
			$note->description=str_slug($note->title, ' ').', '.$textDescription; 
			$note->save(); 
			Theme::setDescription(str_slug($note->title, ' ').', '.$textDescription);
		?> 
	@endif
@endif
<style>
.error{display:block;color:red;}
.groupForm{position:relative;}
#preloader{position:fixed;top:0;left:0;width:100%;height:100%;background-color:#e4e7ea;z-index:10000;}
#status{width:30px;height:30px;position:absolute;left:50%;top:50%;margin:-15px 0 0 -15px;font-size:32px;}
#preloaderInBox{position:absolute;top:0;left:0;width:100%;height:100%;background-color:#e4e7ea;z-index:10000;opacity:0.8;}
</style>
@if($ads=='true')
	<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
	<script>
		 (adsbygoogle = window.adsbygoogle || []).push({
			  google_ad_client: "ca-pub-6739685874678212",
			  enable_page_level_ads: true
		 });
	</script>
@endif
<div class="container">
    <div class="row">
		<div class="col-12 col-md-8">
			<div class="form-group">
				<h1 class="text-light">{{$keyword}}</h1> 
			</div>
			@if(count($searchProduct))
			<div class="form-group">
				<small class="text-light">Khoảng {{AppHelper::instance()->price($total)}} kết quả</small>
			</div>
			<div class="form-group">
				<ul class="list-group"> 
					<?
						$i=0; 
					?>
					@foreach($searchProduct as $item)
					<?
						$i++; 
						if($item['type']=='category'){
							$link=route('show.category',array(config('app.url'),str_replace(' ','+',$item['title']))); 
							$rel=''; 
							$target=''; 
						}else if($item['type']=='video'){
							$link=route('video.show',array(config('app.url'),$item['id'],str_slug($item['title'],'-'))); 
							$rel='';
							$target=''; 
						}else if($item['type']=='post'){
							$link=route('post.show',array(config('app.url'),$item['id'],str_slug($item['title'],'-'))); 
							$rel='';
							$target=''; 
						}else if($item['type']=='channel'){
							$link=route('profile',array(config('app.url'),$item['id'])); 
							$rel='';
							$target=''; 
						}else if($item['type']=='affiliate'){
							$link=route('product.show',array(config('app.url'),$item['id'],str_slug($item['title'], '-'))); 
							$rel='';
							$target='target="_blank"'; 
						}else if($item['type']=='company'){
							$link=route('company.show',array(config('app.url'),$item['id'],str_slug($item['title'],'-'))); 
							$rel='';
							$target=''; 
						}else if($item['type']=='news'){
							$link=route('news.show',array(config('app.url'),$item['id'],str_slug($item['title'],'-'))); 
							$rel='';
							$target=''; 
						}else if($item['type']=='site'){
							$link=route('site.show',array(config('app.url'),$item['id'],str_slug($item['title'],'-'))); 
							$rel='';
							$target='target="_blank"'; 
						}else{
							$link=''; 
						}
					?>
					<li class="list-group-item">
						<h4><a href="{{$link}}" {{$rel}} {{$target}}>{{str_limit(strip_tags(html_entity_decode($item['title']),''), $limit = 150, $end = '...')}}</a></h4>
						@if($item['type']=='affiliate')
						<small><strong class="text-danger">Giá bán: {{AppHelper::instance()->price($item['price'])}}<sup>đ</sup></strong>, bán tại: {{$item['sub_type']}}</small>
						<p><small class="text-muted">{{str_limit($item['url'], $limit = 100, $end = '...')}}</small></p>
						@elseif($item['type']=='video')
						@if(!empty($item['thumb']))
							<img class="float-left pr-2" width="120" src="{{$item['thumb']}}" title="{{$item['title']}}" alt="{{$item['title']}}">
						@endif
						<small>{{str_limit(strip_tags(html_entity_decode($item['description']),''), $limit = 255, $end = '...')}}</small>
						@elseif($item['type']=='site')
						<small>{{str_limit(strip_tags(html_entity_decode($item['description']),''), $limit = 255, $end = '...')}}</small>
						@elseif($item['type']=='post')
							@if(!empty($item['media']) && count($item['media']))
								@if($item['media'][0]['type']=='image')
									<a href="{{$link}}" {{$rel}} {{$target}}><img class="float-left pr-2" width="120" src="{{$item['media'][0]['url_xs']}}" title="{{$item['title']}}" alt="{{$item['title']}}"></a>
								@endif
							@endif
							<small>{{str_limit(strip_tags(html_entity_decode($item['content']),''), $limit = 255, $end = '...')}}</small>
						@elseif($item['type']=='company')
							{{$item['address']}}
						@endif
					</li>
					@if($i==3 && $ads=='true')
						<div class="form-group mt-2">
							<ins class="adsbygoogle"
								 style="display:block"
								 data-ad-client="ca-pub-6739685874678212"
								 data-ad-slot="7536384219"
								 data-ad-format="auto"></ins>
							<script>
							(adsbygoogle = window.adsbygoogle || []).push({});
							</script>
						</div>
					@endif
					@endforeach
				</ul>
			</div>
			@if($ads=='true')
				<div class="form-group">
					<ins class="adsbygoogle"
						 style="display:block"
						 data-ad-client="ca-pub-6739685874678212"
						 data-ad-slot="7536384219"
						 data-ad-format="auto"></ins>
					<script>
					(adsbygoogle = window.adsbygoogle || []).push({});
					</script>
				</div>
			@endif
			@if(count($categorySearch))
				<div class="form-group">
				<h5>Các từ khóa liên quan đến {{$keyword}}</h5>
				@foreach($categorySearch as $item)
					<span class="badge badge-light"><a href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$item['title'])))}}"><span class="">{!!$item['title']!!} </span></a> </span>
				@endforeach
				</div>
			@endif
			<div class="form-group">
				{!! $searchProduct->render() !!}
			</div>
			@endif
			@if(!empty($type) && $type=='category' && count($getNote))
			<div class="form-group">
				<div class="alert alert-info">
					<strong>Cung cấp</strong> thông tin liên quan đến <strong>{!!$note->title!!}</strong> bao gồm các trang web, hình ảnh, video, tin tức, sản phẩm, dịch vụ... 
					@if(count($getNote)>0)
						<?
							$a=0; 
						?>
						<p>Bạn có thể xem các thông tin chi tiết về @foreach($getNote as $item)<?$a++;?>@if($a<=5 && $item['_id']!=$note->id && !empty($item['title'])){{$item['title']}}, @endif @endforeach</p> 
					@endif
				</div>
			</div>
			@endif
		</div>
		<div class="col-12 col-md-4">
			@if($ads=='true')
				<div class="form-group">
					<ins class="adsbygoogle"
						 style="display:block"
						 data-ad-client="ca-pub-6739685874678212"
						 data-ad-slot="7536384219"
						 data-ad-format="auto"></ins>
					<script>
					(adsbygoogle = window.adsbygoogle || []).push({});
					</script>
				</div>
			@endif
		</div>
	</div>
  </div>
@partial('footer') 
<?
	$dependencies = array(); 
	Theme::asset()->writeScript('loadLazy',' 
	', $dependencies);
?>