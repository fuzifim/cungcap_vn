<?
Theme::setTitle('Danh sách bài đăng đang hoạt động'); 
Theme::setSearch('post:active'); 
Theme::setSearchDisable('disable'); 
Theme::setType('website'); 
?>
@partial('header') 
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<div class="container">
	<div class="form-group">
		<h1 class="text-light">Tin đã đăng</h1> 
	</div>
	<nav aria-label="breadcrumb">
		<ol class="breadcrumb mb5" itemscope itemtype="http://schema.org/BreadcrumbList">
			<li class="breadcrumb-item" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem"><a itemscope itemtype="http://schema.org/Thing"
   itemprop="item" href="{{route('index',config('app.url'))}}"><span class="" itemprop="name">Cung Cấp</span></a></li> 
		</ol> 
	</nav>
	<div class="form-group">
		<div class="row">
			<div class="col-md-8">
				<ul class="nav nav-tabs">
				  <li class="nav-item">
					<a class="nav-link active" href="#">Đã đăng</a>
				  </li>
				  <li class="nav-item">
					<a class="nav-link disabled" href="{{route('post.me',array(config('app.url'),'trash'))}}"><span class="text-light">Thùng rác</span></a>
				  </li>
				</ul>
				@if(count($getNote)>0) 
					<div class="form-group" id="postForm">
					@foreach($getNote as $item)
						<div class="list-group-item">
							@if(!empty($item->media) && count($item->media))
								<img src="{{$item->media[0]['url_xs']}}" width="32" class="float-left">
							@endif
							<a href="{{route('post.show',array(config('app.url'),$item->id,str_slug($item->title, '-')))}}">{!!$item->title!!}</a> 
							<div class="btn-group d-flex" role="group">
								<a class="btn btn-sm btn-success" href="{{route('post.edit',array(config('app.url'),$item->id))}}">Sửa</a>
								<button class="btn btn-sm btn-danger postDelete" type="button" data-id="{{$item->id}}">Xóa</button>
							</div>
						</div>
					@endforeach 
					</div>
					<div class="form-group">
						{!! html_entity_decode($getNote->links()) !!}
					</div>
				@endif
			</div>
			<div class="col-md-4">
				
			</div>
		</div>
	</div>
</div> 
@partial('footer') 
<?
	Theme::asset()->themePath()->add('style-gritter', 'library/gritter/jquery.gritter.css'); 
	Theme::asset()->themePath()->add('js-gritter', 'library/gritter/jquery.gritter.min.js');
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
		$("#postForm").on("click",".postDelete",function() {
			if(confirm("Bạn có chắc muốn xóa?")){
				$(this).parent().closest(".list-group-item").remove(); 
				var formData = new FormData();
				formData.append("postId", $(this).attr("data-id")); 
				$.ajax({
					url: "'.route("post.delete",array(config('app.url'),'trash')).'",
					headers: {"X-CSRF-TOKEN": $("meta[name=_token]").attr("content")},
					type: "post",
					cache: false,
					contentType: false,
					processData: false,
					data: formData,
					dataType:"json",
					success:function(result){ 
					console.log(result); 
						jQuery.gritter.add({
							title: "Thông báo!",
							text: result.message, 
							class_name: "growl-success",
							sticky: false,
							time: ""
						});
						window.location.href = "'.route('post.me',array(config('app.url'),'trash')).'";
					},
					error: function(result) {
					}
				}); 
				return false; 
			}
		}); 
	', $dependencies);
?>