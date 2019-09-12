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
Theme::setDescription(str_slug($note->title, ' ').', '.htmlspecialchars($note->address).', '.$note->attribute['tax_code']);
Theme::setType('article'); 
Theme::setCanonical(route('company.show',array(config('app.url'),$note->id,str_slug($note->title, '-')))); 
if(!empty($noteParent->title)){
$string='<script type="application/ld+json">
		{
			"@context": "http://schema.org",
			"@type": "NewsArticle",
			"mainEntityOfPage":{
				"@type":"WebPage",
				"@id":"'.route('company.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))).'"
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
				"@id":"'.route('company.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))).'"
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
		<ol class="breadcrumb mb5" itemscope itemtype="http://schema.org/BreadcrumbList">
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('index',config('app.url'))}}"><span class="" itemprop="name">Cung Cấp</span></a></li> 
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('company.list',config('app.url'))}}"><span class="" itemprop="name">Company</span></a></li> 
			@if(!empty($noteParent->title))<li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"  itemprop="item" href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$noteParent->title)))}}"><span itemprop="name">{!!$noteParent->title!!}</span></a></li>@endif
		</ol> 
	</nav>
	<div class="form-group">
		<div class="row">
			<div class="col-md-8">
				<div class="alert alert-info">
					<strong>Cung cấp thông tin {!!$note->title!!}</strong> - {!!$note->attribute['title_en']!!} có địa chỉ tại {!!$note->address!!}. Mã số thuế {!!$note->attribute['tax_code']!!}
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
				<div class="card form-group">
					<div class="card-body">
						@if(!empty($noteImage))
							<img class="img-fluid" src="{{$noteImage}}" alt="{!!$note->title!!}" title="{!!$note->title!!}">
						@endif
						<h1 class="card-title">{!!$note->title!!}</h1> 
						<div class="card-text">
							<h3 class="subtitle"><strong>{!!$note->attribute['title_en']!!}</strong></h3>
							@if(!empty($note->address))<p><strong>Địa chỉ:</strong> <i class="glyphicon glyphicon-map-marker"></i> {!!$note->address!!}</p>@endif
							@if(!empty($note->attribute['tax_code']))
								<p><i class="fa fa-barcode"></i> <strong>Mã số thuế:</strong> {!!$note->attribute['tax_code']!!}</p>
							@endif
							@if(!empty($note->attribute['admin_name']))
								<p><i class="fa fa-user"></i> <strong>Người đại diện:</strong> {!!$note->attribute['admin_name']!!}</p>
							@endif
							@if(!empty($note->attribute['ngay_cap']))
								<p><i class="glyphicon glyphicon-time"></i> <strong>Ngày cấp:</strong> {!!$note->attribute['ngay_cap']!!}</p>
							@endif
							<p>
								<small><span class="time-update"><i class="glyphicon glyphicon-time"></i> Cập nhật: {!!$note->created_at!!}</span></small> 
								<small><i class="glyphicon glyphicon-eye-open"></i> <span class="post-view text-danger">{{$note->view}} lượt xem</span></small>
							</p>
						</div>
					</div>
				</div> 
				<div class="form-group">
					<div class="btn-group d-flex" role="group">
					<a class="btn btn-primary siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://www.facebook.com/sharer/sharer.php?u=".route('company.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))))}}'><span class="fa fa-facebook"></span> Share on Facebook</a> 
					<a class="btn btn-info siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://twitter.com/share?url=".route('company.show',array(config('app.url'),$note->id,str_slug($note->title, '-'))))}}'><span class="fa fa-twitter"></span> Share on Twitter</a>
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
				@if(count($newCompany)>0)
					<div class="card form-group">
						@foreach($newCompany as $item)
							<div class="list-group-item">
								<strong><a class="title" href="{{route('company.show',array(config('app.url'),$item['_id'],str_slug($item['title'], '-')))}}">{!!$item['title']!!}</a></strong>
							</div>
						@endforeach 
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