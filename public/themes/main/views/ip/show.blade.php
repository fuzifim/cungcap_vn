<?php
	Theme::setTitle($note->title); 
	Theme::setSearch('ip:'.$note->title); 
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
   itemprop="item" href="{{route('ip.list',config('app.url'))}}"><span class="" itemprop="name">Ip</span></a></li> 
		</ol> 
	</nav>
	<div class="row">
		<div class="col-12 col-md-9">
			<div class="card form-group mt-2">
				<div class="card-body p-2">
					<h1>IP: {{$note->title}}</h1>
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
					@if(!empty($note->description))
						<?
							$jsonDecode=json_decode($note->description); 
						?>
						@if(!empty($jsonDecode->as))
							<p><strong>AS: </strong>{!!$jsonDecode->as!!}</p>
						@endif
						@if(!empty($jsonDecode->city))
							<p><strong>City: </strong>{!!$jsonDecode->city!!}</p>
						@endif
						@if(!empty($jsonDecode->country))
							<p><strong>Country: </strong>{!!$jsonDecode->country!!}</p>
						@endif
						@if(!empty($jsonDecode->countryCode))
							<p><strong>Country code: </strong>{!!$jsonDecode->countryCode!!}</p>
						@endif
						@if(!empty($jsonDecode->isp))
							<p><strong>Isp: </strong>{!!$jsonDecode->isp!!}</p>
						@endif
						@if(!empty($jsonDecode->lat))
							<p><strong>Lat: </strong>{!!$jsonDecode->lat!!}</p>
						@endif
						@if(!empty($jsonDecode->lon))
							<p><strong>Lon: </strong>{!!$jsonDecode->lon!!}</p>
						@endif
						@if(!empty($jsonDecode->org))
							<p><strong>Org: </strong>{!!$jsonDecode->org!!}</p>
						@endif
						@if(!empty($jsonDecode->query))
							<p><strong>Query: </strong>{!!$jsonDecode->query!!}</p>
						@endif
						@if(!empty($jsonDecode->region))
							<p><strong>Region: </strong>{!!$jsonDecode->region!!}</p>
						@endif
						@if(!empty($jsonDecode->regionName))
							<p><strong>Region name: </strong>{!!$jsonDecode->regionName!!}</p>
						@endif
						@if(!empty($jsonDecode->status))
							<p><strong>Status: </strong>{!!$jsonDecode->status!!}</p>
						@endif
						@if(!empty($jsonDecode->timezone))
							<p><strong>Timezone: </strong>{!!$jsonDecode->timezone!!}</p>
						@endif
						@if(!empty($jsonDecode->zip))
							<p><strong>Zip: </strong>{!!$jsonDecode->zip!!}</p>
						@endif
					@endif
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
		</div>
		<div class="col-12 col-md-3">
			@if(count($newIp))
				<h3 class="text-light">New ip</h3>
				<ul class="list-group">
					@foreach($newIp as $ip)
						<li class="list-group-item"><a href="{{route('ip.show',array(config('app.url'),$ip['title']))}}">{{$ip['title']}}</a></li>
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
		$("#summernote").summernote({
			placeholder: "Bạn đang cung cấp gì?",
			tabsize: 2,
			height: 100, 
			minHeight: 200, 
			maxHeight: 500,
			focus: true     
		  });
	', $dependencies);
?>