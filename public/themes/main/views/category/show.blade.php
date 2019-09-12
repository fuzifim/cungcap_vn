<?
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
Theme::setSearch($note->title); 
Theme::setType('website'); 
Theme::setCanonical(route('show.category',array(config('app.url'),str_replace(' ','+',$note->title)))); 
$string='<script type="application/ld+json">
		{
		"@context" : "http://schema.org",
		"@type" : "WebSite",
		"name" : "'.htmlspecialchars($note->title).'",
		"alternateName" : "",
		"url" : "'.route('show.category',array(config('app.url'),str_replace(' ','+',$note->title))).'"
		}
		</script>
	'; 
Theme::set('appendHeader', $string);
?>
@partial('header') 
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<div class="container">
	<div class="form-group">
		<h1 class="text-light">{!!$note->title!!}</h1> 
		<p class="text-light">Cung cấp thông tin về <strong>{!!$note->title!!}</strong></p>
	</div>
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb mb5" itemscope itemtype="http://schema.org/BreadcrumbList">
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('index',config('app.url'))}}"><span class="" itemprop="name">Cung Cấp</span></a></li> 
			@if(!empty($noteParent->title))<li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"  itemprop="item" href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$noteParent->title)))}}"><span itemprop="name">{!!$noteParent->title!!}</span></a></li>@endif
		</ol> 
	</nav>
	<div class="form-group">
		<div class="row">
			<div class="col-md-8">
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
				@if(count($searchProduct))
				<div class="form-group">
					<div class="text-light">
						<h4>Sản phẩm</h4>
						<span>liên quan đến {!!$note->title!!}</span>
					</div>
					<ul class="list-group"> 
						@foreach($searchProduct as $product)
						<li class="list-group-item">
							<h2><a href="{{route('product.show',array(config('app.url'),$product['id'],str_slug($product['title'], '-')))}}" target="_blank">{!!$product['title']!!}</a></h2>
							<div class="btn-group">
								<span class="btn btn-secondary btn-sm"><strong>Giá bán: </strong>{{AppHelper::instance()->price($product['price'])}}<sup>đ</sup></span> 
								@if(!empty($product['discount']) && $product['discount']!=$product['price'])
								<span class="btn btn-secondary btn-sm"><strong>Khuyến mãi: </strong>{{AppHelper::instance()->price($product['discount'])}}<sup>đ</sup></span>
								@endif
							</div>
								
						</li>
						@endforeach
					</ul>
				</div>
				<div class="form-group">
					{!! $searchProduct->render() !!}
				</div>
				@endif
				<div class="form-group">
					<? $a=0; ?>
					@foreach($getNote as $item)
						@if($item['type']=='image') 
							<? $a++;?>
							@if($a==1)
							<img class="img-fluid" id="showImageLarge" src="https:{{$item['attribute']['image']}}" alt="{{$item['title']}}" title="{{$item['title']}}"> 
							<h5><a class="text-light" id="showImageLargeLink" href="{{route('go.to',array(config('app.url'),$item['_id'],str_slug($item['title'], '-')))}}" rel="nofollow" target="blank"><span class="text-light">{{$item['title']}}</span></a></h5>
							@endif
						@endif 
					@endforeach 
				</div> 
				<div class="form-group" id="thumbImage">
					<div class="row row-pad-5">
					<? $b=0;$activeAdsAfterImage='false'; ?>
					@foreach($getNote as $item)
						@if($item['type']=='image') 
							<? $b++;$activeAdsAfterImage='true';?>
							@if($b<=6)
							<div class="col col-md-2 mb-2">
								<a class="showImageLink" href="https:{{$item['attribute']['image']}}" data-image="https:{{$item['attribute']['image']}}" data-title="{{$item['title']}}" data-url="{{route('go.to',array(config('app.url'),$item['_id'],str_slug($item['title'], '-')))}}"><img class="img-fluid" src="https:{{$item['attribute']['thumb']}}" alt="{{$item['title']}}" title="{{$item['title']}}"></a> 
							</div>
							@endif
						@endif 
					@endforeach 
					</div>
				</div>
				<div class="form-group">
					<div class="btn-group d-flex" role="group">
					<a class="btn btn-primary siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://www.facebook.com/sharer/sharer.php?u=".route('show.category',array(config('app.url'),str_replace(' ','+',$note->title))))}}'><span class="fa fa-facebook"></span> Share on Facebook</a> 
					<a class="btn btn-info siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://twitter.com/share?url=".route('show.category',array(config('app.url'),str_replace(' ','+',$note->title)))."&text=".$note->title)}}'><span class="fa fa-twitter"></span> Share on Twitter</a>
					</div>
				</div>
				@if($activeAdsAfterImage=='true')
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
				<div class="siteList">
					<ul class="list-group"> 
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
								@if($activeAdsAfterImage=='false' && $k==3)
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
								<li class="list-group-item">
									<h2><img src="https://www.google.com/s2/favicons?domain={{$item['attribute']['domain']}}" alt="{{$item['attribute']['domain']}}" title="{{$item['attribute']['domain']}}"><a class="siteLink" data-url='{{json_encode($item["link"])}}' href="{{route('site.show',array(config('app.url'),$item['_id'],str_slug($item['title'], '-')))}}"> {{$item['title']}} </a></h2>
									<p><small>{!!str_limit($item['link'], $limit = 50, $end = '...')!!} <a class="text-muted" href="http://{{$item['attribute']['domain']}}.d.{{config('app.url')}}" target="_blank"> {!!$item['attribute']['domain']!!} </a></small></p>
									<p>{{$item['description']}} </p>
								</li>
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
					</ul>
				</div>
				@foreach($getNote as $item)
					@if($item['type']=='category')
					<a class="badge badge-light" href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$item['title'])))}}"><span class="">{!!$item['title']!!} </span></a> 
					@endif
				@endforeach 
				<div class="form-group">
				@foreach($getNote as $item)
					@if($item['type']=='domain')
						<li class="list-group-item">
							<h2><a href="http://{{$item['domain']}}.d.{{config('app.url')}}">{!!$item['domain']!!}</a></h2>
						</li>
					@endif
				@endforeach 
				</div>
				@if(count($getNote)>0) 
				<div class="form-group">
					{!! html_entity_decode($getNote->links()) !!}
				</div>
				@endif
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
			</div>
			<div class="col-md-4">
				@if(count($getNote)>0)
					<div class="card form-group">
						<ins class="adsbygoogle"
							 style="display:block"
							 data-ad-client="ca-pub-6739685874678212"
							 data-ad-slot="7536384219"
							 data-ad-format="auto"></ins>
						<script>
						(adsbygoogle = window.adsbygoogle || []).push({});
						</script>
					</div>
					<div class="card">
						@foreach($getNote as $item)
							@if($item['type']=='video')
							<div class="list-group-item">
								<div class="row row-pad-5">
									<div class="col-5 col-md-5">
										<a class="image" href="{{route('video.show',array(config('app.url'),$item['_id'],str_slug($item['title'], '-')))}}">
											<img src="https:@if(!empty($item['thumb'])){{$item['thumb']}}@else{{$item['image']}}@endif" class="img-fluid lazy" alt="{!!$item['title']!!}" title="{!!$item['title']!!}">
										</a>
									</div>
									<div class="col-7 col-md-7">
										<strong><a class="title" href="{{route('video.show',array(config('app.url'),$item['_id'],str_slug($item['title'], '-')))}}">{!!$item['title']!!}</a></strong>
									</div>
								</div>
							</div>
							@endif
						@endforeach 
					</div>
				@endif
			</div>
		</div>
	</div>
</div> 
@partial('footer') 
<?
	$dependencies = array(); 
	Theme::asset()->writeScript('loadLazy',' 
		$(".siteLink").click(function(){
			window.open(jQuery.parseJSON($(this).attr("data-url")),"_blank");
			return false; 
		}); 
		$(".showImageLink").click(function(){
			$("#showImageLarge").attr("src",$(this).attr("data-image")); 
			$("#showImageLarge").attr("title",$(this).attr("data-title")); 
			$("#showImageLarge").attr("alt",$(this).attr("data-title")); 
			$("#showImageLargeLink").attr("href",$(this).attr("data-url")); 
			$("#showImageLargeLink").text($(this).attr("data-title")); 
			return false; 
		});
	', $dependencies);
?>