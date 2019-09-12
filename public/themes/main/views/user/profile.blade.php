<?
	Theme::setTitle($user->name); 
	Theme::setSearch($user->name); 
	if(!empty($user->attribute['avatar'])){
		Theme::setImage($user->attribute['avatar']); 
	}
	Theme::setCanonical(route('profile',array(config('app.url'),$user->id))); 
?>
@partial('header') 
<div class="container">
	<div class="form-group mt-2">
		<div class="row row-pad-5">
			<div class="col-12 col-sm-3">
				<div class="card form-group">
					<div class="card-body p-2">
						<div class="form-group">
						@if(!empty($user->attribute['avatar']))
							<img class="rounded img-fluid mx-auto d-block" src="{{$user->attribute['avatar']}}">
						@else
							<img class="rounded img-fluid mx-auto d-block" src="https://cungcap.net/themes/main/assets/img/no-avatar.png">
						@endif
						</div>
						<div class="form-group">
							<strong>{{$user->name}}</strong>
							<p><small>Join: {{AppHelper::instance()->time_request($user->created_at)}}</small></p>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12 col-sm-9">
				@if(count($postFromUser))
					@foreach($postFromUser->chunk(3) as $chunk)
					<div class="row row-pad-5 m-0">
						@foreach($chunk as $noteRelated)
						<div class="col-12 col-sm-4 col-md-4">
							<div class="card form-group">
								<div class="card-body p-2">
									@if(!empty($noteRelated['media'])&&count($noteRelated['media']))
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