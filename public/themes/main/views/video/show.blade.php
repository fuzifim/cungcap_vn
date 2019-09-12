<?
if(AppHelper::instance()->checkWordCC($note->title)){
	Theme::setTitle(config('app.name').' video '.htmlspecialchars($note->title)); 
}else{
	Theme::setTitle(htmlspecialchars($note->title)); 
}
Theme::setSearch($note->title); 
Theme::setImage('https:'.$note->image); 
Theme::setType('article'); 
Theme::setCanonical(route('video.show',array(config('app.url'),$note->id,str_slug($note->title, '-')))); 
?>
@partial('header') 
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<div class="container">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb mt-2" itemscope itemtype="http://schema.org/BreadcrumbList">
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('index',config('app.url'))}}"><span class="" itemprop="name">Cung Cấp</span></a></li> 
			@if(!empty($noteParent->title))<li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"  itemprop="item" href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$noteParent->title)))}}"><span itemprop="name">{!!$noteParent->title!!}</span></a></li>@endif
		</ol> 
	</nav>
	<div class="form-group">
		<div class="row">
			<div class="col-md-8">
				<div class="card form-group">
					<div class="card-body">
						<div class="embed-responsive embed-responsive-16by9">
							<iframe width="640" height="320" class="embed-responsive-item" src="https://www.youtube.com/embed/{{$note->yid}}" allowfullscreen></iframe>
						</div> 
						<h1 class="card-title">{!!$note->title!!}</h1> 
						<p class="card-text">{!!$note->description!!}</p>
					</div>
				</div> 
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
				<div class="form-group">
					<div class="alert alert-info">
						<strong>Cung cấp</strong> thông tin liên quan đến <strong>{!!$note->title!!}</strong> bao gồm các trang web, hình ảnh, video, tin tức, sản phẩm, dịch vụ...  
						@if(count($getNoteRelate)>0)
							<?
								$a=0; 
							?>
							<p>Bạn có thể xem các thông tin chi tiết về @foreach($getNoteRelate as $item)<?$a++;?>@if($a<=5 && $item->id!=$note->id){!!$item->title!!}, @endif @endforeach</p> 
						@endif
					</div>
				</div>
				@if(count($searchProduct))
				<div class="form-group">
					<ul class="list-group"> 
						@foreach($searchProduct as $product)
						<li class="list-group-item">
							<h2><a href="{{route('product.show',array(config('app.url'),$product['id'],str_slug($product['title'], '-')))}}">{!!$product['title']!!}</a></h2>
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
					<div class="btn-group d-flex" role="group">
					<a class="btn btn-primary siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://www.facebook.com/sharer/sharer.php?u=".route('video.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))))}}'><span class="fa fa-facebook"></span> Share on Facebook</a> 
					<a class="btn btn-info siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://twitter.com/share?url=".route('video.show',array(config('app.url'),$note->id,str_slug($note->title, '-')))."&text=".$note->title)}}'><span class="fa fa-twitter"></span> Share on Twitter</a>
					</div>
				</div>
				@if(count($getNoteRelate)>0)
					<div class="siteList form-group">
						<ul class="list-group"> 
							<? $k=0; ?>
							@foreach($getNoteRelate as $item)
								@if($item->type=='site')
									<li class="list-group-item">
									<h2><img src="https://www.google.com/s2/favicons?domain={{$item['attribute']['domain']}}" alt="{{$item['attribute']['domain']}}" title="{{$item['attribute']['domain']}}"><a class="siteLink" data-url='{{json_encode($item["link"])}}' href="{{route('site.show',array(config('app.url'),$item['_id'],str_slug($item['title'], '-')))}}"> {!!$item['title']!!} </a></h2>
									<p><small>{!!str_limit($item['link'], $limit = 50, $end = '...')!!} <a class="text-muted" href="http://{{$item['attribute']['domain']}}.d.{{config('app.url')}}" target="_blank"> {!!$item['attribute']['domain']!!} </a></small></p>
									<p>{!!$item['description']!!} </p>
								</li>
								@endif
							@endforeach 
						</ul>
					</div>
					<div class="form-group">
						<? $a=0; ?>
						@foreach($getNoteRelate as $item)
							@if($item->type=='image') 
								<? $a++;?>
								@if($a==1)
								<img class="img-fluid" id="showImageLarge" src="https:{{$item->attribute['image']}}" alt="{!!$item->title!!}" title="{!!$item->title!!}"> 
								<h5><a class="text-light" id="showImageLargeLink" href="{{route('go.to',array(config('app.url'),$item->id,str_slug($item->title, '-')))}}" rel="nofollow" target="blank"><span class="text-light">{!!$item->title!!}</span></a></h5>
								@endif
							@endif 
						@endforeach 
					</div> 
					<div class="form-group">
						<div class="row row-pad-5">
						<? $b=0; ?>
						@foreach($getNoteRelate as $item)
							@if($item->type=='image') 
								<? $b++;?>
								@if($b<=6)
								<div class="col col-md-2 mb-2">
									<a class="showImageLink" href="https:{{$item->attribute['image']}}" data-image="https:{{$item->attribute['image']}}" data-title="{!!$item->title!!}" data-url="{{route('go.to',array(config('app.url'),$item->id,str_slug($item->title, '-')))}}"><img class="img-fluid" src="https:{{$item->attribute['thumb']}}" alt="{!!$item->title!!}" title="{!!$item->title!!}"></a> 
								</div>
								@endif
							@endif 
						@endforeach 
						</div>
					</div>
				@endif
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
				@if(count($getNoteRelate)>0)
					<div class="card">
						<? $k=0; $textDescription='';?>
						@foreach($getNoteRelate as $item)
							@if($item->type=='video' && $item->id!=$note->id) 
							<? $k++; ?>
							@if($k==1 && !Theme::has('description'))
								<? $textDescription.=(str_replace("\n", "", str_replace("\r", "", $item->title)));?> 
							@endif 
							@if($k==2 && !Theme::has('description'))
								<? $textDescription.=', '.(str_replace("\n", "", str_replace("\r", "", $item->title)));?> 
							@endif 
							@if($k==3 && !Theme::has('description'))
								<? $textDescription.=', '.(str_replace("\n", "", str_replace("\r", "", $item->title)));?> 
							@endif 
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
						@if(!Theme::has('description'))
							<?Theme::setDescription(str_slug($note->title, ' ').', '.$textDescription);?> 
						@else 
							<? Theme::setDescription(str_slug($note->title, ' ').', '.htmlspecialchars($note->description));?>
						@endif
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
	if(!empty($noteParent->title)){
	$string='<script type="application/ld+json">
			{
				"@context": "http://schema.org",
				"@type": "NewsArticle",
				"mainEntityOfPage":{
					"@type":"WebPage",
					"@id":"'.route('video.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))).'"
				},
				"headline": "'.htmlspecialchars($note->title).'",
				"description": "'.htmlspecialchars($note->description).'",
				"image": {
					"@type": "ImageObject",
					"url": "https:'.$note->image.'",
					"width" : 720,
					"height" : 480
				},
				"datePublished": "'.date('Y-m-d\TH:i:sP', strtotime($note->created_at)).'",
				"dateModified": "'.date('Y-m-d\TH:i:sP', strtotime($note->updated_at)).'",
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
					"@id":"'.route('video.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))).'"
				},
				"headline": "'.htmlspecialchars($note->title).'",
				"description": "'.htmlspecialchars($note->description).'",
				"image": {
					"@type": "ImageObject",
					"url": "https:'.$note->image.'",
					"width" : 720,
					"height" : 480
				},
				"datePublished": "'.date('Y-m-d\TH:i:sP', strtotime($note->created_at)).'",
				"dateModified": "'.date('Y-m-d\TH:i:sP', strtotime($note->updated_at)).'",
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