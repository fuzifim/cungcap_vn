<?
if(!empty($note->title)){
	Theme::setTitle($note->domain.' '.htmlspecialchars($note->title)); 
}else{
	Theme::setTitle($note->domain); 
}
if(!empty($note->attribute['whois'])){
	$decodeWhois=json_decode($note->attribute['whois']); 
}
if(!empty($note->description)){
	Theme::setDescription($note->domain.' '.$note->description); 
}else if(!empty($note->attribute['whois'])){
	$description=''; 
	$description.=$note->domain; 
	if(!empty($decodeWhois->creationDate)){
		$description.=' created at '.$decodeWhois->creationDate; 
	}
	if(!empty($decodeWhois->expirationDate)){
		$description.=' and expiration date '.$decodeWhois->expirationDate; 
	}
	if(!empty($decodeWhois->registrar)){
		$description.=' Registrar by '.$decodeWhois->registrar; 
	} 
	if(!empty($decodeWhois->nameServer) && !empty($decodeWhois->nameServer[0])){
		$description.=' Name server: '.$decodeWhois->nameServer[0]; 
	} 
	if(!empty($decodeWhois->nameServer[1])){
		$description.=' and '.$decodeWhois->nameServer[1]; 
	}
	if(!empty($note->attribute['rank'])){
		$description.=' It has a global traffic rank of '.$note->attribute['rank'].' in the world'; 
	} 
	if(!empty($note->attribute['country_code']) && !empty($note->attribute['rank_country'])){
		$description.=' and rank at '.$note->attribute['country_code'].' is '.$note->attribute['rank_country']; 
	} 
	Theme::setDescription($description); 
}else{
	Theme::setDescription('Site '.$note->domain.' cannot find any description infomation, please find back again! '); 
}
if(!empty($note->attribute['content'])){
	$domainContent=json_decode($note->attribute['content']); 
}else{
	$domainContent=array();
}
Theme::setSearch($note->domain); 
Theme::setType('website'); 
Theme::setCanonical('http://'.$note->domain.'.d.'.config('app.url')); 
$string='<script type="application/ld+json">
		{
		"@context" : "http://schema.org",
		"@type" : "WebSite",
		"name" : "'.Theme::get('title').'",
		"alternateName" : "",
		"url" : "'.'http://'.$note->domain.'.d.'.config('app.url').'"
		}
		</script>
	'; 
Theme::set('appendHeader', $string);
$ads='true';
if(!empty($note->attribute['ads']) && $note->attribute['ads']=='disable'){
	$ads='false';
}else if($note->status=='blacklist' && $note->status=='disable' && $note->status=='delete'){
	$ads='false';
}
?>
@partial('header') 
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
	<div class="form-group">
		<h1 class="text-light"><img src="https://www.google.com/s2/favicons?domain={{$note->domain}}" alt="{{$note->domain}}" title="{{$note->domain}}"> <strong> {!!$note->domain!!}</strong></h1>
		@if(Auth::check())
			<p>Status: {{$note->status}} @if(!empty($note->attribute['ads']))- Ads: {{$note->attribute['ads']}}@endif</p>
			<div class="btn-group">
				<button type="button" class="btn btn-primary btn-sm statusDomain" data-type="disableads">Disable ads</button> 
				<button type="button" class="btn btn-primary btn-sm statusDomain" data-type="activeads">Active ads</button> 
				<button type="button" class="btn btn-primary btn-sm statusDomain" data-type="blacklist">Blacklist</button> 
				<button type="button" class="btn btn-primary btn-sm statusDomain" data-type="active">Active</button> 
				<button type="button" class="btn btn-primary btn-sm statusDomain" data-type="pending">Pending</button> 
				<button type="button" class="btn btn-danger btn-sm statusDomain" data-type="delete">Delete</button>
			</div>
		@endif
	</div>
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb mt-2" itemscope itemtype="http://schema.org/BreadcrumbList">
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('domain.list',config('app.url'))}}"><span class="" itemprop="name">Domain</span></a></li> 
			@if(!empty($noteParent->title))
			<li class="breadcrumb-item active" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"  itemprop="item" href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$noteParent->title)))}}"><span itemprop="name">{!!$noteParent->title!!}</span></a></li>
			@endif
		</ol> 
	</nav>
	<div class="form-group">
		<div class="row">
			<div class="col-md-8">
				@if(!empty($note->title) || !empty($note->description))
				<div class="card form-group">
					<div class="card-body">
						<h5>Information</h5>
						@if(!empty($note->title))<h2><strong>{!!str_limit($note->title, 100)!!}</strong></h2>@endif 
						@if(!empty($note->description))<p>{!!$note->description!!}</p>@endif 
					</div>
				</div> 
				@else 
					<div class="card form-group">
						<div class="card-body">
							Site {{$note->domain}} cannot find any description infomation, please find back again! 
						</div>
					</div>
				@endif
				<div class="form-group">
					<div class="btn-group d-flex" role="group">
					<a class="btn btn-primary siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://www.facebook.com/sharer/sharer.php?u=http://".$note->domain.".d.".config('app.url'))}}'><span class="fa fa-facebook"></span> Share Facebook</a> 
					<a class="btn btn-info siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://twitter.com/share?url=http://".$note->domain.".d.".config('app.url'))}}'><span class="fa fa-twitter"></span> Share Twitter</a>
					<a class="btn btn-danger siteLink w-100" href="javascript:void(0);" data-url='{{json_encode("https://plus.google.com/share?url=http://".$note->domain.".d.".config('app.url'))}}'><span class="fa fa-twitter"></span> Share Google+</a>
					</div>
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
				<div class="form-group">
					<a class="btn btn-primary btn-block siteLink" id="linkContinue" data-url='{{json_encode('http://'.$note->domain)}}' href="{{route('go.to',array(config('app.url'),$note->id,str_slug($note->domain, '-')))}}" rel="nofollow" target="blank">Visit this site click here
					<p><strong>{{$note->domain}}</strong></p>
					</a>
				</div>
				@if(count($siteRelate))
					<div class="card form-group">
						<div class="card-body">
							Finded related to {{$note->domain}} including: 
							<?$a=0;?>
							@foreach($siteRelate as $item)<?$a++;?>@if($a<=5 && !empty($item['title'])){{$item['title']}}, @endif @endforeach
							you can see the information below
						</div>
					</div>
					<div class="form-group">
						<div class="form-group"><strong class="text-light">Related site to {{$note->domain}}</strong></div>
						<div class="list-group"> 
						@foreach($siteRelate as $site)
							<li class="list-group-item">
								<h2><img src="https://www.google.com/s2/favicons?domain={{$site['attribute']['domain']}}" alt="{{$site['attribute']['domain']}}" title="{{$site['attribute']['domain']}}"><a class="siteLink" data-url='{{json_encode($site["link"])}}' href="{{route('site.show',array(config('app.url'),$site['_id'],str_slug($site['title'], '-')))}}"> {{$site['title']}} </a></h2>
								<p><small>{!!str_limit($site['link'], $limit = 50, $end = '...')!!} <a class="text-muted" href="http://{{$site['attribute']['domain']}}.d.{{config('app.url')}}" target="_blank"> {!!$site['attribute']['domain']!!} </a></small></p>
								<p>{{$site['description']}} </p>
							</li>
						@endforeach
						</div>
					</div>
				@endif
				@if(!empty($note->attribute['whois']))
					<p class="alert alert-info"><strong>{{$note->domain}}</strong>@if(!empty($decodeWhois->creationDate)) created at {{$decodeWhois->creationDate}}  @endif @if(!empty($decodeWhois->expirationDate)) and expiration date {{$decodeWhois->expirationDate}}. @endif @if(!empty($decodeWhois->registrar)) Registrar by <strong>{!!$decodeWhois->registrar!!}</strong>.@endif @if(!empty($decodeWhois->nameServer)) Name server: @if(!empty($decodeWhois->nameServer[0])){{$decodeWhois->nameServer[0]}}@endif @if(!empty($decodeWhois->nameServer[1]))and {{$decodeWhois->nameServer[1]}}@endif @endif. @if(!empty($note->attribute['rank'])) It has a global traffic rank of {{$note->attribute['rank']}} in the world @if(!empty($note->attribute['country_code']) && !empty($note->attribute['rank_country'])) and rank at <strong>{{$note->attribute['country_code']}}</strong> is {{$note->attribute['rank_country']}}@endif @endif</p>
				@endif
				@if(!empty($note->attribute['dns_record']) && count($note->attribute['dns_record']))
					<ul class="list-group form-group mb-2">
						@foreach($note->attribute['dns_record'] as $record)
							<li class="list-group-item">
							@if(!empty($record['host']))
								<p><strong>Host: </strong>{{$record['host']}}</p>
							@endif
							@if(!empty($record['class']))
								<p><strong>Class: </strong>{{$record['class']}}</p>
							@endif
							@if(!empty($record['ttl']))
								<p><strong>Ttl: </strong>{{$record['ttl']}}</p>
							@endif
							@if(!empty($record['type']))
								<p><strong>Type: </strong>{{$record['type']}}</p>
							@endif
							@if(!empty($record['ip']))
								<p><strong>Ip: </strong><a href="{{route('ip.show',array(config('app.url'),$record['ip']))}}" target="_blank">{{$record['ip']}}</a></p>
							@endif
							@if(!empty($record['ipv6']))
								<p><strong>Ipv6: </strong><a href="{{route('ip.show',array(config('app.url'),$record['ipv6']))}}" target="_blank">{{$record['ipv6']}}</a></p>
							@endif
							@if(!empty($record['txt']))
								<p><strong>Txt: </strong>{{$record['txt']}}</p>
							@endif
							@if(!empty($record['target']))
								<p><strong>Target: </strong>{{$record['target']}}</p>
							@endif
							</li>
						@endforeach
					</ul>
				@endif
				@if(!empty($domainContent->basic_info))
					<div class="form-group">
						<ul class="nav nav-tabs">
							<li class="nav-item"><a class="nav-link active" href="#basicInfo" data-toggle="tab"><strong>Basic</strong></a></li>
							<li class="nav-item"><a class="nav-link" href="#info" data-toggle="tab"><strong>Website</strong></a></li>
							<li class="nav-item"><a class="nav-link" href="#SemRush" data-toggle="tab"><strong>SemRush Metrics</strong></a></li>
							<li class="nav-item"><a class="nav-link" href="#dns" data-toggle="tab"><strong>DNS Report</strong></a></li>
							<li class="nav-item"><a class="nav-link" href="#ipAddress" data-toggle="tab"><strong>IP</strong></a></li>
							<li class="nav-item"><a class="nav-link" href="#whois" data-toggle="tab"><strong>Whois</strong></a></li>
						</ul>
						<div class="card tab-content mb10">
							@if(!empty($domainContent->basic_info))
							<div class="tab-pane active" id="basicInfo">
								@if($ads=='true')
									<div class="row">
										<div class="col-md-6">
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
										<div class="col-md-6">
											{!!$domainContent->basic_info!!}
										</div>
									</div>
								@else 
									{!!$domainContent->basic_info!!}
								@endif
							</div>
							@endif
							@if(!empty($domainContent->website_info))
							<div class="card-body tab-pane" id="info">
								{!!$domainContent->website_info!!}
							</div>
							@endif
							@if(!empty($domainContent->semrush_metrics))
							<div class="card-body tab-pane" id="SemRush">
								{!!$domainContent->semrush_metrics!!}
							</div>
							@endif
							@if(!empty($domainContent->dns_report))
							<div class="card-body tab-pane" id="dns">
								<div class="table-responsive">
								{!!$domainContent->dns_report!!}
								</div>
							</div>
							@endif
							@if(!empty($domainContent->ip_address_info))
							<div class="card-body tab-pane" id="ipAddress">
								{!!$domainContent->ip_address_info!!}
							</div>
							@endif
							@if(!empty($domainContent->whois_record))
							<div class="card-body tab-pane" id="whois">
								{!!$domainContent->whois_record!!}
							</div>
							@endif
						</div>
					</div>
				@endif
			</div>
			<div class="col-md-4">
				@if($ads=='true')
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
				@endif
				@if(count($getNoteRelate)>0)
				<div class="siteList form-group">
					<ul class="list-group"> 
						<? $k=0; ?>
						@foreach($getNoteRelate as $item)
							@if($item['type']=='domain' && $item['_id']!=$note->id)
								<li class="list-group-item">
									<h3><a href="http://{{$item['domain']}}.d.{{config('app.url')}}">{!!$item['domain']!!}</a></h3>
								</li>
							@endif
						@endforeach 
					</ul>
				</div>
				@endif
				@if(count($newDomain)>0)
				<div class="siteList form-group">
					<ul class="list-group"> 
						<? $k=0; ?>
						@foreach($newDomain as $item)
							@if($item->type=='domain' && $item->id!=$note->id)
								<li class="list-group-item">
									<h5><a href="http://{{$item->domain}}.d.{{config('app.url')}}">{!!$item->domain!!}</a></h5>
								</li>
							@endif
						@endforeach 
					</ul>
				</div>
				@endif
			</div>
		</div>
	</div>
</div>
<div class="modal" id="ModalFacebook">
	<div class="modal-dialog">
		<div class="modal-content">

			<!-- Modal Header -->
			<div class="modal-header">
				<h4 class="modal-title">Like trang và chia sẻ để thấy nội dung</h4>
				<button type="button" class="close" data-dismiss="modal" id="timeLeft">&times;</button>
			</div>

			<!-- Modal body -->
			<div class="modal-body text-center">
				<p>Nhấn vào nút <strong>thích trang</strong> để thấy nội dung <strong>{!! $note->domain !!}</strong></p>
				<div class="fb-page" data-href="https://www.facebook.com/cungcap.net/" data-tabs="" data-width="" data-height="" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true"><blockquote cite="https://www.facebook.com/cungcap.net/" class="fb-xfbml-parse-ignore"></blockquote></div>
				<p>Hoặc nhấn <a href="https://www.youtube.com/channel/UCTR65Hn65TWPupGBWUMkzuA?sub_confirmation=1" target="_blank" rel="nofollow" class="label label-success"><i class="glyphicon glyphicon-hand-right"></i> vào đây</a> và sau đó xác nhận đăng ký kênh bấm vào <strong>Đăng ký</strong> để xem nội dung {!! $note->domain !!}</p>
				@if($ads=='true')
					<div class="modal-footer text-center">
						<div class="container form-group">
							<ins class="adsbygoogle"
								 style="display:block"
								 data-ad-client="ca-pub-6739685874678212"
								 data-ad-slot="7536384219"
								 data-ad-format="auto"></ins>
							<script>
								setTimeout(function(){(adsbygoogle = window.adsbygoogle || []).push({})}, 1000);
							</script>
						</div>
					</div>
				@endif
			</div>

		</div>
	</div>
</div>
@partial('footer') 
<?
	Theme::asset()->add('core-js-summernote', 'https://code.jquery.com/jquery-3.3.1.js');
	$dependencies = array(); 
	Theme::asset()->writeScript('loadLazy','
		$("#ModalFacebook").modal("show");
        var count = 100;
        setInterval(function(){
            document.getElementById("timeLeft").innerHTML = count;
            if (count == 0) {
                $("#ModalFacebook").modal("hide");
                document.getElementById("timeLeft").innerHTML = "&times;";
            }
            count--;
        },1000);
        $("#ModalFacebook").modal({backdrop: "static", keyboard: false});

		$(".siteLink").click(function(){
			window.open(jQuery.parseJSON($(this).attr("data-url")),"_blank");
			return false; 
		}); 
		$(".statusDomain").click(function(){
			var formData = new FormData();
			formData.append("action", $(this).attr("data-type")); 
			$.ajax({
				url: "https://'.config('app.url').'/domain/'.$note->domain.'",
				headers: {"X-CSRF-TOKEN": $("meta[name=_token]").attr("content")},
				type: "post",
				contentType: false,
				processData: false,
				data: formData,
				dataType:"json",
				success:function(result){ 
					console.log(result); 
					location. reload(true);
				},
				error: function(result) {
					
				}
			}); 
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