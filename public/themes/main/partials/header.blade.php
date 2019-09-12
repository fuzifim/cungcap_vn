<nav class="navbar navbar-expand-lg navbar-light bg-light">
	<a class="navbar-brand" href="{{route('index',config('app.url'))}}">
		<img src="https://cungcap.net/themes/main/assets/img/logo-red-blue.svg" height="50" class="d-inline-block align-top" alt="Cung Cấp" title="Cung Cấp">
	</a>
	<a class="btn btn-sm btn-danger d-block d-sm-none" href="{{route('post.add',config('app.url'))}}" rel="nofollow">Đăng tin</a>
	<a class="btn btn-sm btn-success d-block d-sm-none" href="https://taoweb.cungcap.net" rel="nofollow" target="_blank">Tạo website</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
		<span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="navbarNav">
		<ul class="nav navbar-nav ml-auto">
		<li class="nav-item active">
			<a class="btn btn-sm btn-danger mr-2 mb-2 d-block" href="{{route('post.add',config('app.url'))}}">Đăng tin miễn phí</a>
		</li>
		<li class="nav-item">
			<a class="btn btn-sm btn-success mr-2 mb-2 d-block" href="https://taoweb.cungcap.net" target="_blank" rel="nofollow">Tạo website</a>
		</li>
		@if(Auth::check())
		  <li class="nav-item dropdown">
			<a class="btn btn-sm btn-default dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
			  {{Auth::user()->name}}
			</a>
			<div class="dropdown-menu" aria-labelledby="navbarDropdown">
			  <a class="dropdown-item" href="{{route('post.me',array(config('app.url'),'active'))}}">Tin đã đăng</a>
			  <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="{{route('channel.me',array(config('app.url'),'active'))}}">Kênh của tôi</a>
			  <a class="dropdown-item active" href="{{route('channel.add',array(config('app.url')))}}">Tạo kênh</a>
			   <div class="dropdown-divider"></div>
			  <a class="dropdown-item" href="{{route('logout',config('app.url'))}}">Đăng xuất</a>
			</div>
		  </li>
		@else 
			<li class="nav-item">
				<a class="btn btn-sm btn-default" href="{{route('login',config('app.url'))}}">Đăng nhập</a>
			</li>
		@endif
		</ul>
	</div>
</nav>
@if(!Theme::has('searchDisable'))
<form class="form-group" id="searchform" action="{{route("search.type",array(config('app.url'),'get'))}}" method="get">
	<div class="card-body row no-gutters align-items-center">
		<div class="col">
			<input class="form-control form-control-lg form-control-borderless" name="v" id="searchAll" type="text" placeholder="@if(Theme::has('search')){{ Theme::get('search') }}@else Tìm kiếm... @endif" value="@if(Theme::has('search')){{ Theme::get('search') }}@endif">
			<input type="hidden" name="t" id="searchType" value="">
			<input type="hidden" name="i" id="searchId" value="">
		</div>
		<div class="col-auto">
			<button class="btn btn-lg btn-success" type="submit">Tìm</button>
		</div>
	</div>
</form>
<?
	$dependencies = array(); 
	Theme::asset()->writeScript('searchAll','
		if($("#searchAll").length>0){ 
			$("#searchAll").autocomplete({ 
				serviceUrl: "'.route("search.type",array(config('app.url'),'all')).'",
				type:"GET",
				paramName:"q",
				dataType:"json",
				minChars:2,
				deferRequestBy:100,
				onSearchComplete: function(){
					$(".autocomplete-suggestions").css({
						"width":+$("#searchAll").outerWidth()
					});
				},
				onSelect: function (suggestion) {
					console.log(suggestion); 
					$("#searchType").val(suggestion.type); 
					$("#searchId").val(suggestion.id); 
					$("#searchAll").val(suggestion.value); 
					$("#searchform").submit();
				}
			});
		}
	', $dependencies);
?>
@endif