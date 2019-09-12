<?
	Theme::setTitle($channel->title); 
	Theme::setSearch($channel->title); 
	if(!empty($channel->logo['url_thumb'])){
		Theme::setImage('https:'.$channel->logo['url_thumb']); 
	}
	Theme::setCanonical(route('profile',array(config('app.url'),$channel->id))); 
	//dd($channel); 
?>
@partial('header') 
<div class="container">
	<div class="form-group mt-2">
		<div class="row row-pad-5">
			<div class="col-12 col-sm-3">
				<div class="card form-group">
					<div class="card-body p-2">
						<div class="form-group">
						@if(!empty($channel->logo['url_small']))
							<img class="rounded img-fluid mx-auto d-block" src="{{$channel->logo['url_small']}}">
						@else
							<img class="rounded img-fluid mx-auto d-block" src="https://cungcap.net/themes/main/assets/img/no-avatar.png">
						@endif
						</div>
						<div class="form-group">
							<h1><strong>{{$channel->title}}</strong></h1>
							<p><small>Join: {{AppHelper::instance()->time_request(date("Y-m-d H:i:s", strtotime($channel->created_at->toDateTime()->format(DATE_RSS).' UTC')))}}</small></p>
						</div>
						<hr>
						@if(!empty($channel->address) && count($channel->address))
						<div class="form-group">
							@foreach($channel->address as $address)
							<p class="text-primary">{!!$address['address']!!}</p>
							@endforeach
						</div>
						@endif
						<div class="form-group">
						{!!strip_tags(html_entity_decode($channel->description),"<b>,<strong>,<br>")!!}
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-9">
				@if(!empty($channel->banner) && count($channel->banner))
					<div id="carouselExampleControls" class="carousel slide form-group" data-ride="carousel">
					  <div class="carousel-inner">
						<?$i=0;?>
						@foreach($channel->banner as $media)
							<?$i++;?>
							<div class="carousel-item @if($i==1) active @endif">
							  <img class="d-block w-100" src="{{$media['url_thumb']}}" alt="">
							</div>
						@endforeach
					  </div>
					  @if(count($channel->banner)>1)
					  <a class="carousel-control-prev" href="#carouselExampleControls" role="button" data-slide="prev">
						<span class="carousel-control-prev-icon" aria-hidden="true"></span>
						<span class="sr-only">Previous</span>
					  </a>
					  <a class="carousel-control-next" href="#carouselExampleControls" role="button" data-slide="next">
						<span class="carousel-control-next-icon" aria-hidden="true"></span>
						<span class="sr-only">Next</span>
					  </a>
					  @endif
					</div>
				@endif
				@if(count($postFromUser))
					@foreach($postFromUser->chunk(3) as $chunk)
					<div class="row row-pad-5 m-0">
						@foreach($chunk as $noteRelated)
						<div class="col-12 col-sm-4 col-md-4">
							<div class="card form-group">
								<div class="card-body p-2">
									@if(!empty($noteRelated['media']) && count($noteRelated['media']))
										@if($noteRelated['media'][0]['type']=='image')
										<a class="image" href="{{route('post.show',array(config('app.url'),$noteRelated['_id'],str_slug($noteRelated['title'], '-')))}}">
											<img src="@if(!empty($noteRelated['media'][0]['url_xs'])){{$noteRelated['media'][0]['url_xs']}}@elseif(!empty($noteRelated['media'][0]['url_thumb'])){{$noteRelated['media'][0]['url_xs']}}@elseif(!empty($noteRelated['media'][0]['url'])){{$noteRelated['media'][0]['url']}}@endif" class="img-fluid lazy" alt="{{$noteRelated['title']}}" title="{{$noteRelated['title']}}">
										</a>
										@elseif($noteRelated['media'][0]['type']=='video')
											<div align="center" class="embed-responsive embed-responsive-16by9">
												<video class="embed-responsive-item" controls>
													<source src="{{$noteRelated['media'][0]['url']}}" type="video/mp4">
													Your browser does not support the video tag.
												</video>
											</div>
										@endif
									@endif
									<p><strong><a class="title" href="{{route('post.show',array(config('app.url'),$noteRelated['_id'],str_slug($noteRelated['title'], '-')))}}">{{$noteRelated['title']}}</a></strong></p>
									<p><small class="text-muted">{{$noteRelated['view']}} view</small> - <small>{{AppHelper::instance()->time_request(date("Y-m-d H:i:s", strtotime($noteRelated['updated_at']->toDateTime()->format(DATE_RSS).' UTC')))}}</small></p>
								</div>
							</div>
						</div>
						@endforeach
					</div>
					@endforeach
					<div class="form-group">
						{!! html_entity_decode($postFromUser->links()) !!}
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