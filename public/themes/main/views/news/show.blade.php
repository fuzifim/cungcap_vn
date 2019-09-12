<?
if(AppHelper::instance()->checkWordCC($note->title)){
	Theme::setTitle(config('app.name').' '.htmlspecialchars($note->title)); 
}else{
	Theme::setTitle(htmlspecialchars($note->title)); 
}
$noteImage=''; 
if(!empty($note->image)){ 
	$noteImage='https:'.$note->image; 
}else if(count($getNoteRelate)){
	$i=0;
	foreach($getNoteRelate as $item){
		if($item->type=='image'){
			$i++; 
			if($i==1){ 
				$noteImage='https:'.$item->attribute['image']; 
			}
		}
	}
}
Theme::setImage($noteImage); 
Theme::setSearch($note->title); 
Theme::setDescription(str_slug($note->title, ' ').', '.htmlspecialchars($note->description));
Theme::setType('article'); 
Theme::setCanonical(route('news.show',array(config('app.url'),$note->id,str_slug($note->title, '-')))); 
if(!empty($noteParent->title)){
$string='<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "NewsArticle",
			"mainEntityOfPage":{
				"@type":"WebPage",
				"@id":"'.route('news.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))).'"
			},
			"headline": "'.htmlspecialchars($note->title).'",
			"description": "'.htmlspecialchars($note->description).'",
			"image": {
				"@type": "ImageObject",
				"url": "https:'.$noteImage.'",
				"width" : 720,
				"height" : 480
			},
			"author": {
				"@type": "Person",
				"name": "Cung Cấp"
			},
			"publisher": {
				"@type": "Organization",
				"name": "Cung Cấp",
				"logo": {
					"@type": "ImageObject",
					"url": "https://cungcap.vn/themes/main/assets/img/logo-red-blue.svg"
				}
			}
		}
	</script>
	<script type="application/ld+json">
	{
		"@context": "http://schema.org",
		"@type": "BreadcrumbList",
		"itemListElement": [
			{
			"@type": "ListItem",
			"position": 1,
			"item": {
				"@id": "'.route('index',config('app.url')).'",
				"name": "Cung Cấp"
			}
		},{
			"@type": "ListItem",
			"position": 2,
			"item": {
				"@id": "'.route('show.category',array(config('app.url'),str_replace(' ','+',$noteParent->title))).'",
				"name": "'.htmlspecialchars($noteParent->title).'"
			}
		}
		]
	}
	</script>
	'; 
}else{
	
$string='<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "NewsArticle",
			"mainEntityOfPage":{
				"@type":"WebPage",
				"@id":"'.route('news.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))).'"
			},
			"headline": "'.htmlspecialchars($note->title).'",
			"description": "'.htmlspecialchars($note->description).'",
			"image": {
				"@type": "ImageObject",
				"url": "https:'.$noteImage.'",
				"width" : 720,
				"height" : 480
			},
			"author": {
				"@type": "Person",
				"name": "Cung Cấp"
			},
			"publisher": {
				"@type": "Organization",
				"name": "Cung Cấp",
				"logo": {
					"@type": "ImageObject",
					"url": "https://cungcap.vn/themes/main/assets/img/logo-red-blue.svg"
				}
			}
		}
	</script>
	'; 
}
Theme::set('appendHeader', $string);
?>
@partial('header') 
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<div class="container">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb mt-2" itemscope itemtype="http://schema.org/BreadcrumbList">
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('index',config('app.url'))}}"><span class="" itemprop="name">Cung Cấp</span></a></li> 
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('news.list',config('app.url'))}}"><span class="" itemprop="name">News</span></a></li> 
			@if(!empty($noteParent->title))<li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"  itemprop="item" href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$noteParent->title)))}}"><span itemprop="name">{!!$noteParent->title!!}</span></a></li>@endif
		</ol> 
	</nav>
	<div class="form-group">
		<div class="row">
			<div class="col-md-8">
				<div class="card form-group">
					<div class="card-body">
						@if(!empty($noteImage))
							<img class="img-fluid" src="{{$noteImage}}" alt="{!!$note->title!!}" title="{!!$note->title!!}">
						@endif
						<h1 class="card-title">{!!$note->title!!}</h1> 
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
						<div class="card-text postContent">
							{!!AppHelper::instance()->addNofollow(html_entity_decode($note->content),config('app.url'),true)!!} 
							@if(!empty($note->attribute['source_url']))
							<?
								$parsedUrl=parse_url($note->attribute['source_url']); 
							?>
							@if(!empty($parsedUrl['host']))
							Nguồn: {{$parsedUrl['host']}} 
							@endif
							@endif
						</div>
					</div>
				</div> 
				<div class="form-group">
					<div class="btn-group d-flex" role="group">
					<a class="btn btn-primary siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://www.facebook.com/sharer/sharer.php?u=".route('news.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))))}}'><span class="fa fa-facebook"></span> Share on Facebook</a> 
					<a class="btn btn-info siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://twitter.com/share?url=".route('news.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))))}}'><span class="fa fa-twitter"></span> Share on Twitter</a>
					</div>
				</div>
			</div>
			<div class="col-md-4"> 
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
				<div class="list-group">
					@foreach($getNoteNews as $item)
						@if(!empty($item->image))
							<div class="list-group-item">
								<div class="row row-pad-5">
									<div class="col-5 col-md-5">
										<a class="image" href="{{route('news.show',array(config('app.url'),$item->id,str_slug($item->title, '-')))}}">
											<img src="@if(!empty($item->image_thumb)){{$item->image_thumb}}@else{{$item->image}}@endif" class="img-fluid lazy" alt="{!!$item->title!!}" title="" >
										</a>
									</div>
									<div class="col-7 col-md-7">
										<h5 class="postTitle nomargin"><a class="title" href="{{route('news.show',array(config('app.url'),$item->id,str_slug($item->title, '-')))}}">{!!$item->title!!}</a></h5>
										<small><span><i class="glyphicon glyphicon-time"></i> {!!$item->updated_at!!}</span></small> 
									</div>
								</div>
							</div>
						@else 
							<div class="list-group-item form-group">
								<h5 class="postTitle"><a class="title" href="{{route('news.show',array(config('app.url'),$item->id,str_slug($item->title, '-')))}}">{!!$item->title!!}</a></h5>
									<small><span><i class="glyphicon glyphicon-time"></i> {!!$item->updated_at!!}</span></small> 
							</div>
						@endif
					@endforeach
				</div>
				@if(count($getNoteRelate)>0)
					<div class="card">
						@foreach($getNoteRelate as $item)
							@if($item->type=='video')
							<div class="list-group-item">
								<div class="row row-pad-5">
									<div class="col-5 col-md-5">
										<a class="image" href="{{route('video.show',array(config('app.url'),$item->id,str_slug($item->title, '-')))}}">
											<img src="https:@if(!empty($item->thumb)){{$item->thumb}}@else{{$item->image}}@endif" class="img-fluid lazy" alt="{!!$item->title!!}" title="{!!$item->title!!}">
										</a>
									</div>
									<div class="col-7 col-md-7">
										<strong><a class="title" href="{{route('video.show',array(config('app.url'),$item->id,str_slug($item->title, '-')))}}">{!!$item->title!!}</a></strong>
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