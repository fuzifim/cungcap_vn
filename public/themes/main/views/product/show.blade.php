<?php
	Theme::setTitle(config('app.name').' '.$note->title); 
	Theme::setSearch('product:'.$note->title); 
	Theme::setCanonical(route('product.show',array(config('app.url'),$note->id,str_slug($note->title, '-')))); 
	Theme::setImage($note->image); 
?>
@partial('header') 
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<script>
     (adsbygoogle = window.adsbygoogle || []).push({
          google_ad_client: "ca-pub-6739685874678212",
          enable_page_level_ads: true
     });
</script>
<div class="container">
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb mt-2" itemscope itemtype="http://schema.org/BreadcrumbList">
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('index',config('app.url'))}}"><span class="" itemprop="name">Cung Cấp</span></a></li> 
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('product.list',config('app.url'))}}"><span class="" itemprop="name">Products</span></a></li> 
		</ol> 
	</nav>
	<div class="row">
		<div class="col-12 col-md-8">
			<div class="card form-group mt-2">
				<div class="card-body p-2">
					<div class="form-group">
						<a href="{{route('go.to',array(config('app.url'),$note->id,str_slug($note->title, '-')))}}" rel="nofollow" target="blank"><img class="img-fluid mx-auto d-block" id="showImageLarge" src="{{$note->image}}" alt="{!!$note->title!!}" title="{!!$note->title!!}"></a> 
					</div>
					<h1>{{$note->title}}</h1>
					<div class="form-group">
						@if(!empty($note->category))
						<p><strong>Danh mục: </strong>{{$note->category}}</p>
						@endif
						<p><strong>Nhà cung cấp: </strong>{{$note->sub_type}}</p>
						<p><strong>Mã sản phẩm: </strong>{{$note->sku}}</p>
						<p><strong>Giá bán: </strong>{{AppHelper::instance()->price($note->price)}}<sup>đ</sup></p>
						@if(!empty($note->discount) && $note->discount!=$note->price)
							<p><strong>Giá khuyến mãi:</strong> {{AppHelper::instance()->price($note->discount)}}<sup>đ</sup></p>
						@endif
						<p><strong>Link: </strong><small class="text-info">{{$note->url}}</small></p> 
						<p><strong>Image url: </strong><small>{{$note->image}}</small></p> 
						@if(!empty($note->description))
							{!!str_limit($note->description, $limit = 250, $end = '...')!!}
						@endif
						<p><a class="btn btn-primary float-right" href="{{route('go.to',array(config('app.url'),$note->id,str_slug($note->title, '-')))}}" rel="nofollow" target="blank">Tới nhà cung cấp</a></p>
					</div>
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
			@if(count($getNoteRelate)>0) 
				<h3 class="text-light">Sản phẩm khác cùng danh mục</h3>
				<div class="form-group">
				@foreach($getNoteRelate as $item)
					<div class="list-group-item">
						<strong><a class="title" href="{{route('product.show',array(config('app.url'),$item['_id'],str_slug($item['title'], '-')))}}">{!!$item['title']!!}</a></strong>
					</div>
				@endforeach 
				</div>
			@endif
			@if(count($searchProduct))
				<div class="form-group">
					<h3 class="text-light">Sản phẩm liên quan được tìm thấy</h3>
					<ul class="list-group"> 
						@foreach($searchProduct as $product)
						<li class="list-group-item">
							<h2><a href="{{route('product.show',array(config('app.url'),$product['id'],str_slug($product['title'], '-')))}}">{!!$product['title']!!}</a></h2>
							<div class="btn-group">
								@if(!empty($product['price']))<span class="btn btn-secondary btn-sm"><strong>Giá bán: </strong>{{AppHelper::instance()->price($product['price'])}}<sup>đ</sup></span> @endif
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
		</div>
		<div class="col-12 col-md-4">
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
			@if(count($newProduct))
				<h3 class="text-light">New product</h3>
				<ul class="list-group">
					@foreach($newProduct as $product)
						<li class="list-group-item p-2"><a href="{{route('product.show',array(config('app.url'),$product['_id'],str_slug($product['title'], '-')))}}">{{$product['title']}}</a></li>
					@endforeach
				</ul>
			@endif
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