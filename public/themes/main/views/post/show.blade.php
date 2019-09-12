<?
	Theme::setTitle($note->title); 
	Theme::setSearch($note->title); 
	if(!empty($note->media) && count($note->media) && $note->media[0]['type']=='image'){
		Theme::setImage('https:'.$note->media[0]['url_thumb']); 
	}
	Theme::setCanonical(route('post.show',array(config('app.url'),$note->id,str_slug($note->title, '-')))); 
?>
@partial('header') 
<style>
pre{background:#f1f0f0;padding:10px;}
</style>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<div class="container">
	<div class="form-group mt-2">
		<div class="row row-pad-5">
			<div class="col-md-8">
				<div class="card form-group">
					<div class="card-body p-2">
						@if(!empty($note->media) && count($note->media))
							<div id="carouselExampleControls" class="carousel slide form-group" data-ride="carousel">
							  <div class="carousel-inner">
								<?$i=0;?>
								@foreach($note->media as $media)
									<?$i++;?>
									@if($media['type']=='image')
										<div class="carousel-item @if($i==1) active @endif">
										  <img class="d-block w-100" src="{{$media['url_thumb']}}" alt="">
										</div>
									@elseif($media['type']=='video')
										<div align="center" class="embed-responsive embed-responsive-16by9">
											<video class="embed-responsive-item" controls>
												<source src="{{$media['url']}}" type="video/mp4">
												Your browser does not support the video tag.
											</video>
										</div>
									@endif
								@endforeach
							  </div>
							  @if(count($note->media)>1)
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
				<div class="card form-group">
					<div class="card-body p-2">
						<div class="form-group">
							@if(!empty($note->channel->title))
								@if(!empty($note->channel->logo['url_xs']))
									<img class="rounded img-thumbnail" width="50" src="{{$note->channel->logo['url_xs']}}">
								@else
									<img class="rounded" width="50" src="https://{{config('app.url')}}/favicon.png">
								@endif
								<small> đăng trên </small><strong><a href="{{route('profile',array(config('app.url'),$note->channel->id))}}">{{$note->channel->title}}</a></strong>
							@endif
							<small> bởi <span itemprop="author"><a href="{{route('profile',array(config('app.url'),$note->author->id))}}">{{$note->author->name}}</a></span></small>@if(!empty($note->subregion['subregion_name']))<small><span> tại </span><a href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$note->subregion['subregion_name'])))}}">{{$note->subregion['subregion_name']}}</a></small>@endif  @if(!empty($note->region['region_name']))<small> - <a href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$note->region['region_name'])))}}">{{$note->region['region_name']}}</a></small>@endif
							<p><small class="text-danger">{{$note->view}} view</small> - <small class="text-muted" datetime="{!!$note->updated_at!!}" itemprop="datePublished">{{AppHelper::instance()->time_request(date("Y-m-d H:i:s", strtotime($note->updated_at->toDateTime()->format(DATE_RSS).' UTC')))}}</small></p> 
						</div>
						@if(!empty($note->price))
							<h4><span class="badge badge-danger"><strong>{{AppHelper::instance()->price($note->price)}}</strong><sup>đ</sup></span></h4>
						@endif
						<h1 itemprop="name">
							{!!$note->title!!}
							@if(Auth::check())
								<?
									$user=Auth::user(); 
								?>
								@if($user->id==$note->user_id)
								<small><a href="{{route('post.edit',array(config('app.url'),$note->id))}}" class="badge badge-danger"> Sửa</a></small>
								@endif
							@endif
						</h1>
						<div itemprop="description" class="postContent">
							<div class="table-responsive-md form-group">
							{!!AppHelper::instance()->addNofollow(html_entity_decode($note->content),config('app.url'),true)!!}
							</div>
						</div>
						@if(!empty($note->tags))
							<?
							$tagArray=explode(',',$note->tags); 
							?>
							<div class="form-group">
								<small class="">Từ khóa: </small>
								@foreach($tagArray as $tag)
									<span class="badge badge-light"><a href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$tag)))}}"><span class="">{!!$tag!!} </span></a> </span>
								@endforeach
							</div>
						@endif
						<hr>
						@if(!empty($note->user_name))
							<small><strong>{{$note->user_name}}</strong></small><br>
						@endif
						@if(!empty($note->address))
							<small>Địa chỉ: {{$note->address}}</small><br>
						@endif
						@if(!empty($note->email) && !empty($note->hiddenEmail) && $note->hiddenEmail!='hidden')
							<small>Email: {{$note->email}}</small><br>
						@endif
						@if(!empty($note->phone) && !empty($note->hiddenPhone) && $note->hiddenPhone!='hidden')
							<div class="btn-group d-flex" role="group">
								<a class="btn btn-success w-100" href="tel:{{$note->phone}}"><h3>{{$note->phone}}</h3></a>
							</div>
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
				@if(count($getNoteRelate)>=3)
					<h5 class="text-light">Bài khác cùng người đăng</h5>
					@foreach($getNoteRelate->chunk(3) as $chunk)
					@if(count($chunk)>=3)
					<div class="row row-pad-5 m-0">
						@foreach($chunk as $noteRelated)
						<div class="col-12 col-sm-4 col-md-4">
							<div class="card form-group">
								<div class="card-body p-2">
									@if(!empty($noteRelated['media']) && count($noteRelated['media']) && !empty($noteRelated['media'][0]['url_xs']))
										<a class="image" href="{{route('post.show',array(config('app.url'),$noteRelated['_id'],str_slug($noteRelated['title'], '-')))}}">
											<img src="{{$noteRelated['media'][0]['url_xs']}}" class="img-fluid mx-auto d-block lazy" alt="{{$noteRelated['title']}}" title="{{$noteRelated['title']}}">
										</a>
									@endif
									<p><strong><a class="title" href="{{route('post.show',array(config('app.url'),$noteRelated['_id'],str_slug($noteRelated['title'], '-')))}}">{{$noteRelated['title']}}</a></strong></p>
									<p><small class="text-muted">{{$noteRelated['view']}} view</small> - <small>{{AppHelper::instance()->time_request(date("Y-m-d H:i:s", strtotime($noteRelated['updated_at']->toDateTime()->format(DATE_RSS).' UTC')))}}</small></p>
								</div>
							</div>
						</div>
						@endforeach
					</div>
					@endif
					@endforeach
				@endif
			</div>
			<div class="col-md-4">
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
				@if(count($notePostNew))
					<h5 class="text-light">Bài đăng mới</h5>
					<div class="list-group">
					@foreach($notePostNew as $noteRelated)
						@if($noteRelated['_id']!=$note->id)
						<div class="list-group-item p-2">
							@if(!empty($noteRelated['media']) && count($noteRelated['media']))
								<a class="image" href="{{route('post.show',array(config('app.url'),$noteRelated['_id'],str_slug($noteRelated['title'], '-')))}}">
									<img src="{{$noteRelated['media'][0]['url_xs']}}" class="img-fluid mx-auto d-block lazy" alt="{{$noteRelated['title']}}" title="{{$noteRelated['title']}}">
								</a>
							@endif
							<p><strong><a class="title" href="{{route('post.show',array(config('app.url'),$noteRelated['_id'],str_slug($noteRelated['title'], '-')))}}">{{$noteRelated['title']}}</a></strong></p>
							<p><small class="text-muted">{{$noteRelated['view']}} view</small> - <small>{{AppHelper::instance()->time_request(date("Y-m-d H:i:s", strtotime($noteRelated['updated_at']->toDateTime()->format(DATE_RSS).' UTC')))}}</small></p>
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
		if($(".postContent .prettyprint").length){
			jQuery.ajax({
				  url: "https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js",
				  dataType: "script",
				  cache: true
			});
		}
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