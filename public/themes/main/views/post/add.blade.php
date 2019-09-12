<?php
	if(!empty($note->id)){
		$title='Sửa tin'; 
		$buttonSave='Lưu lại'; 
	}else{
		$title='Đăng tin'; 
		$buttonSave='Đăng ngay'; 
	}
	Theme::setTitle($title); 
	Theme::setSearch('post:add'); 
	Theme::setSearchDisable('disable'); 
	$subRegion=''; 
	$addressName=''; 
	if(!empty($note->region['region_id'])){
		$region=\App\Model\Note::find($note->region['region_id']); 
	}else if(!empty($user->attribute['region_history'])){
		$region=\App\Model\Note::find($user->attribute['region_history']); 
	}else{
		$region=\App\Model\Note::where('type','category')->where('sub_type','region')->where('iso',$_SERVER['GEOIP_COUNTRY_CODE'])->first();
	}
	if(!empty($note->subregion['subregion_id'])){
		$subRegion=\App\Model\Note::find($note->subregion['subregion_id']); 
	}else if(!empty($user->attribute['subregion_history'])){
		$subRegion=\App\Model\Note::find($user->attribute['subregion_history']); 
	}
	if(!empty($note->address)){
		$addressName=$note->address; 
	}else if(!empty($user->attribute['address_history'])){
		$addressName=$user->attribute['address_history']; 
	}
	if(!empty($note->user_name)){
		$nameHistory=$note->user_name; 
	}else if(!empty($user->attribute['name_history'])){
		$nameHistory=$user->attribute['name_history']; 
	}else{
		$nameHistory=$user->name; 
	}
	if(!empty($note->phone)){
		$phoneHistory=$note->phone; 
	}else if(!empty($user->attribute['phone_history'])){
		$phoneHistory=$user->attribute['phone_history']; 
	}else if(!empty($user->phone)){
		$phoneHistory=$user->phone; 
	}else{
		$phoneHistory='';
	}
	if(!empty($note->email)){
		$emailHistory=$note->email; 
	}else if(!empty($user->attribute['email_history'])){
		$emailHistory=$user->attribute['email_history']; 
	}else if(!empty($user->email)){
		$emailHistory=$user->email; 
	}else{
		$emailHistory='';
	}
	if(!empty($note->hiddenEmail) && $note->hiddenEmail=='hidden'){
		$hiddenEmail='checked';
	}else if(!empty($user->attribute['hidden_email_history']) && $user->attribute['hidden_email_history']=='hidden'){
		$hiddenEmail='checked';
	}else{
		$hiddenEmail='';
	}
	if(!empty($note->hiddenPhone) && $note->hiddenPhone=='hidden'){
		$hiddenPhone='checked';
	}else if(!empty($user->attribute['hidden_phone_history']) && $user->attribute['hidden_phone_history']=='hidden'){
		$hiddenPhone='checked';
	}else{
		$hiddenPhone='';
	}
	if(!empty($note->tags)){
		$tagArray=explode(',',$note->tags); 
		$tagSelect2=json_encode($tagArray); 
	}else{
		$tagSelect2='[]';
	}
?>
@partial('header') 
<style>
.formTitle{font-size:18px;}
.image-wapper{border:1px solid #ccc;background:#f9f9f9;position: relative;overflow: hidden;} .image-wapper .image-wapper-label{text-align:center;margin-top:20px;color:#1c60a7} .image-wapper .image-wapper-take{text-align:center;margin-top:10px} .image-wapper .image-wapper-take .jfu-btn-upload{width:100%;height:100%;background-color:#f9f9f9;border:none;cursor:pointer}.camera-add-image{color:#ebebeb;font-size:70px}.plus-add-image{position:absolute;top:-17px;left:23px;font-size:25px;color:#5bc3e9} .image-wapper .image-wapper-des{text-align:center;margin-top:10px;line-height:20px;color:#555}.jfu-input-file{position:absolute;top:0;right:0;margin:0;opacity:0;filter:alpha(opacity=0);font-size:23px;direction:ltr;cursor:pointer;min-width:100%;min-height:100%}
.select2 {
max-width:100%!important;
}
.error{display:block;color:red;}
.groupForm{position:relative;}
#preloader{position:fixed;top:0;left:0;width:100%;height:100%;background-color:#e4e7ea;z-index:10000;}
#status{width:30px;height:30px;position:absolute;left:50%;top:50%;margin:-15px 0 0 -15px;font-size:32px;}
#preloaderInBox{position:absolute;top:0;left:0;width:100%;height:100%;background-color:#e4e7ea;z-index:10000;opacity:0.8;}
.note-group-select-from-files {
  display: none;
}
</style>
<div class="container">
	<div class="row row-pad-5">
		<div class="col-12 col-md-9">
			<div class="card form-group mt-2 groupForm">
				<div class="card-body p-2">
					<form id="postForm" class="" method="POST">
						<input type="hidden" name="postId" value="@if(!empty($note->id)){{$note->id}}@endif">
					  <div class="form-group">
						<label for="inputTitle">Tiêu đề</label>
						<input type="text" class="form-control formTitle" id="inputTitle" name="inputTitle" placeholder="Nhập tiêu đề" value="@if(!empty($note->id)){{$note->title}}@endif" required>
					  </div>
					  <div class="form-group">
						<div class="image-wapper mb5">
							<div class="image-wapper-label">
								Thêm Ảnh
							</div>
							<div class="image-wapper-take">
								<div class="jfu-container" id="jfu-plugin-b22da094fc3c-45e7-f95f-6c1af9d2d458"><span class="jfu-btn-upload"><span><span style="position:relative; cursor:pointer"> <i class="fa fa-camera camera-add-image"></i><i class="fa fa-plus-circle plus-add-image"></i></span></span><input id="postMedia" name="postMedia[]" type="file" multiple="" class="input-file jfu-input-file" accept="image/*" data-bind="uploader: UploadOptions"></span></div>
							</div>
						</div>
						<div class="row form-group fileMedia mt-2">
							@if(!empty($note->id))
								@if(!empty($note->media) && count($note->media))
								@foreach($note->media as $key=>$media)
									@if($media['type']=='image')
										<div class="col-3 itemFile" style="position:relative;"><a href="" class="delMediaData" style="position:absolute; bottom:0px; right:0px;" data-id="{{$key}}" data-url="{{$media['url']}}"><span class="label label-danger"><i class="fa fa-trash-o"></i> xóa</span></a><img class="img-thumbnail img-responsive imgItemInsert" src="{{$media['url_xs']}}"></div>
									@elseif($media['type']=='video')
										<div class="col-3 itemFile" style="position:relative;">
										<a href="" class="delMediaData" style="position:absolute; bottom:0px; right:0px;z-index:1; " data-id="{{$media['url']}}"><span class="label label-danger"><i class="fa fa-trash-o"></i> xóa</span></a>
											<a href="" class="btnViewVideo"><span class="btnPlayVideoClickSmall"><i class="glyphicon glyphicon-play"></i></span><img class="img-thumbnail img-responsive" src="{{$media['url']}}.png" ></a>
										</div>
									@endif
								@endforeach
								@endif
							@endif
							</div>
					  </div>
					  <div class="form-group">
						<label for="inputAddress2">Nội dung</label>
						<textarea id="summernote" name="postContent" required>@if(!empty($note->content)){{html_entity_decode($note->content)}}@endif</textarea>
					  </div>
					  <div class="form-group">
						<span id="maxContentPost"></span>
					  </div>
					  <div class="form-group">
						<label for="price">Giá bán</label>
						<input id="price" name="price" type="number" class="form-control" value="@if(!empty($note->price)){{$note->price}}@endif" placeholder="Nhập giá bán...">
						<label class="error" for="price"></label>
						<small class="text-muted"><code>Giá bán là số, không khoảng cách, không dấu ., không ký tự đặc biệt. Nếu không có giá, bạn có thể để trống mục này! </code></small>
					  </div> 
					  <div class="form-group">
						<label for="inputTags">Từ khóa</label>
						<select id="tags" name="tags[]" class="form-control" multiple></select>
					  </div> 
					  @if(count($user->channel))
					  <div class="form-group">
						<label for="channelId">Đăng lên trên</label>
						  <select id="channelId" name="channelId" class="form-control">
							<option value="">---Chọn kênh đăng---</option>
							@foreach($user->channel as $channel)
							@if(!empty($note->id) && $note->channel_id==$channel->id)
								<option value="{{$channel->id}}" selected>{{$channel->title}}</option>
							@else 
								<option value="{{$channel->id}}">{{$channel->title}}</option>
							@endif
							@endforeach
						  </select>
					  </div>
					  @endif
					  <div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputCountry">Quốc gia</label>
							<input type="hidden" name="idRegion" value="{{$region->id}}">
							<input type="hidden" name="regionName" value="{{$region->title}}">
							<input type="hidden" name="regionIso" value="{{mb_strtolower($region->iso)}}">
							<div class="addSelectRegion"></div>
						</div>
						<div class="form-group col-md-6">
							<label for="inputCity">Thành phố</label>
							<input type="hidden" name="idSubRegion" value="@if(!empty($subRegion->id)){!!$subRegion->id!!}@endif">
							<input type="hidden" name="subRegionName" value="@if(!empty($subRegion->title)){!!$subRegion->title!!}@endif">
							<div class="addSelectSubRegion"></div>
						</div>
					  </div>
					  <div class="form-group">
						<label for="inputAddress">Địa chỉ</label>
						<input type="text" class="form-control" id="inputAddress" name="inputAddress" placeholder="Số nhà, đường phố..." value="{{$addressName}}" required>
					  </div> 
					  <div class="form-group">
						<label for="inputName">Tên liên hệ</label>
						<input type="text" class="form-control" id="inputName" name="inputName" placeholder="Tên người liên hệ" value="{{$nameHistory}}" required>
					  </div>
					  <div class="form-row">
						<div class="form-group col-md-6">
							<label for="inputEmail">Email</label>
							<input type="text" class="form-control" id="inputEmail" name="inputEmail" placeholder="Địa chỉ email" value="{{$emailHistory}}" required>
							<div class="form-check">
							  <input class="form-check-input" type="checkbox" id="hiddenEmail" name="hiddenEmail" {{$hiddenEmail}}>
							  <label class="form-check-label" for="hiddenEmail">
								<small>Ẩn địa chỉ email trong bài viết</small>
							  </label>
							</div>
						</div>
						<div class="form-group col-md-6">
							<label for="inputPhone">Số điện thoại</label>
							<input type="text" class="form-control" id="inputPhone" name="inputPhone" placeholder="Số điện thoại" value="{{$phoneHistory}}" required>
							<div class="form-check">
							  <input class="form-check-input" type="checkbox" id="hiddenPhone" name="hiddenPhone" {{$hiddenPhone}}>
							  <label class="form-check-label" for="hiddenPhone">
								<small>Ẩn số điện thoại trong bài viết</small>
							  </label>
							</div>
						</div>
					  </div>
						<div class="text-right">
						@if(!empty($note->id))
							<a class="btn btn-default text-danger" id="postDelete" href="#">Xóa</a> 
							<a class="btn btn-default" href="{{route('post.show',array(config('app.url'),$note->id,str_slug($note->title, '-')))}}">Xem bài</a> 
						@endif
						<button type="submit" class="btn btn-primary" id="savePost">{{$buttonSave}}</button>
					  </div>
					</form>
				</div>
			</div>
		</div>
		<div class="col-12 col-md-3">
			
		</div>
	</div>
</div>
@partial('footer') 
<?
	$dependencies = array(); 
	Theme::asset()->add('core-style-fontaweasome', 'https://use.fontawesome.com/releases/v5.3.1/css/all.css');
	Theme::asset()->add('core-style-summernote', 'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.css');
	Theme::asset()->add('core-js-summernote', 'https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.12/summernote-bs4.js');
	Theme::asset()->add('js-select2-cdn', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js');
	Theme::asset()->add('style-select2', 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.min.css');
	Theme::asset()->themePath()->add('style-flags', 'library/flags/flags.min.css'); 
	Theme::asset()->themePath()->add('style-gritter', 'library/gritter/jquery.gritter.css'); 
	Theme::asset()->themePath()->add('js-gritter', 'library/gritter/jquery.gritter.min.js');
	Theme::asset()->themePath()->add('js-validate', 'js/jquery.validate.min.js');
	Theme::asset()->writeScript('loadLazy',' 
		$(window).bind("beforeunload",function(){
			return "are you sure you want to leave?";
		});
		var s2 =$("#tags").select2({
			placeholder: "Select an item",
			minimumInputLength: 2,
			tags: true, 
			tokenSeparators: [","],
			createSearchChoice: function(term, data) {
				if ($(data).filter(function() {
				  return this.text.localeCompare(term) === 0;
				}).length === 0) {
				  return {
					id: term,
					text: term
				  };
				}
			},
			multiple: true,
			ajax: {
			  url: "'.route("search.type",array(config('app.url'),'category')).'",
			  dataType: "json",
			  delay: 250,
			  data: function (params) {
                    return {
                        q: $.trim(params.term)
                    };
                },
                processResults: function (data) {
                    return {
                        results: data
                    };
                },
			  cache: true
			}, 
			maximumSelectionLength: 10
		});
		var vals='.$tagSelect2.';
		vals.forEach(function(e){
		if(!s2.find("option:contains(" + e + ")").length) 
		  s2.append($("<option>").text(e));
		});

		s2.val(vals).trigger("change"); 
		function uniqId() {
		  return Math.round(new Date().getTime() + (Math.random() * 100));
		}
		$("#postMedia").on("change", function (e) {
			e.preventDefault();
			var files = $("#postMedia").prop("files");  
			var totalFile=files.length; 
			if(totalFile<=5){
				for(var i=0;i<totalFile;i++)
				{
					var id=uniqId(); 
					$(".fileMedia").css("position", "relative"); 
					$(".fileMedia").append("<div class=\"col-3 itemFile itemId-"+id+"\" style=\"position:relative;min-height:70px; \"><div id=\"preloaderInBox\"><span class=\"label label-primary\"></span><div id=\"status\"><i class=\"fa fa-spinner fa-spin\"></i></div></div></div>"); 
					var formData = new FormData(); 
					var itemId=id; 
					formData.append("file", files[i]); 
					formData.append("itemId", id); 
					var xhrRequest = $.ajax({
						xhr: function()
						{
							var xhr = new window.XMLHttpRequest();
							xhr.upload.addEventListener("progress", function(evt){
							  if (evt.lengthComputable) {
								var percentComplete = evt.loaded / evt.total;
								$(".fileMedia .itemId-"+id+" #preloaderInBox span").text(parseInt((100 * evt.loaded / evt.total)) + "%"); 
								if(percentComplete==1){
									$(".fileMedia .itemId-"+id+" #preloaderInBox span").text("Đang xử lý..."); 
								}
							  }
							}, false);
							xhr.addEventListener("progress", function(evt){
							  if (evt.lengthComputable) {
								var percentComplete = evt.loaded / evt.total;
								console.log(percentComplete);
							  }
							}, false);
							return xhr;
						},
						url: "'.route("media.type",array(config('app.url'),'uploadToTmp')).'",
						headers: {"X-CSRF-TOKEN": $("meta[name=_token]").attr("content")},
						type: "post",
						cache: false,
						contentType: false,
						processData: false,
						data: formData,
						dataType:"json",
						success:function(result){ 
							if(result.success==true){
								$("#postMedia").val(""); 
								if(result.mimeType=="image"){
									$(".fileMedia .itemId-"+result.itemId).append("<a href=\"\" class=\"delMedia imgItemInsert\" data-mediaIdRandom=\"\" data-file=\""+result.file_tmp+"\" data-type=\"image\" data-destinationPath=\""+result.destinationPath+"\" style=\"position:absolute; bottom:0px; right:0px;\"><span class=\"label label-danger\"><i class=\"fa fa-trash-o\"></i> xóa</span></a><img class=\"img-thumbnail img-responsive\" src=\""+result.url_small+"\" >"); 
									$(".fileMedia .itemId-"+result.itemId+" #preloaderInBox").remove(); 
								}else if(result.mimeType=="video"){
									var urlMedia="'.config('app.url').'";
									var urlThumb=urlMedia+result.destinationPath+"video/"+result.media_id_random+".png";
									$(".fileMedia .itemId-"+result.itemId).append("<a href=\"\" class=\"imgItemInsert delMedia\" data-mediaIdRandom=\""+result.media_id_random+"\" data-file=\""+result.file_tmp+"\"  data-type=\"video\" data-destinationPath=\""+result.destinationPath+"\" style=\"position:absolute; bottom:0px; right:0px;z-index:1;\"><span class=\"label label-danger\"><i class=\"fa fa-trash-o\"></i> xóa</span></a><a href=\""+result.url+"\" class=\"btnViewVideo\"><span class=\"btnPlayVideoClickSmall\"><i class=\"glyphicon glyphicon-play\"></i></span><img class=\"img-thumbnail img-responsive\" src=\""+urlThumb+"\" ></a>"); 
									$(".fileMedia .itemId-"+result.itemId+" #preloaderInBox").remove(); 
								}else if(result.mimeType=="files"){
									
								}
							}else{
								jQuery.gritter.add({
									title: "Thông báo!",
									text: result.message, 
									class_name: "growl-danger",
									sticky: false,
									time: ""
								});
							}
						},
						error: function(result) {
							jQuery.gritter.add({
								title: "Thông báo!",
								text: "Lỗi không thể tải file, vui lòng thử lại! ", 
								class_name: "growl-danger",
								sticky: false,
								time: ""
							});
						}
					}); 
				}
			}else{
				jQuery.gritter.add({
					title: "Thông báo!",
					text: "Upload tối đa 5 hình ảnh! ", 
					class_name: "growl-danger",
					sticky: false,
					time: ""
				});
			}
		}); 
		$(".fileMedia").on("click",".delMedia",function() {
			$(this).parent().closest(".itemFile").remove(); 
			var formData = new FormData();
			formData.append("fileTmp", $(this).attr("data-file")); 
			formData.append("fileType", $(this).attr("data-type"));  
			formData.append("mediaIdRandom", $(this).attr("data-mediaIdRandom"));
			formData.append("destinationPath", $(this).attr("data-destinationPath")); 
			$.ajax({
				url: "'.route("media.type",array(config('app.url'),'delTmp')).'",
				headers: {"X-CSRF-TOKEN": $("meta[name=_token]").attr("content")},
				type: "post",
				cache: false,
				contentType: false,
				processData: false,
				data: formData,
				dataType:"json",
				success:function(result){ 
				},
				error: function(result) {
				}
			}); 
			return false; 
		}); 
		$(".fileMedia").on("click",".delMediaData",function() {
			if(confirm("Bạn có chắc muốn xóa?")){
				var mediaId= $(this).attr("data-id"); 
				var formData = new FormData();
				formData.append("mediaId", mediaId); 
				formData.append("mediaUrl", $(this).attr("data-url")); 
				formData.append("postId", $("input[name=postId]").val()); 
				$(this).parent().closest(".itemFile").remove(); 
				$(".fileMedia").append("<div id=\"preloaderInBox\"><div id=\"status\"><i class=\"fa fa-spinner fa-spin\"></i></div></div>"); 
				$.ajax({ 
					url: "'.route("media.type",array(config('app.url'),'delMediaPost')).'",
					type: "post", 
					headers: {"X-CSRF-TOKEN": $("meta[name=_token]").attr("content")},
					cache: false,
					contentType: false,
					processData: false,
					data: formData,
					dataType:"json",
					success:function(result){
						console.log(result); 
						$(".fileMedia #preloaderInBox").css("display", "none"); 
						jQuery.gritter.add({
							title: "Thông báo!",
							text: result.message, 
							class_name: "growl-success",
							sticky: false,
							time: ""
						});
					},
					error: function(result) {
					}
				}); 
			}
			return false; 
		});
		getRegions(); 
		$(".addSelectRegion").on("change",".selectRegion",function() {
			getSubregion($(this).val()); 
			$("input[name=regionIso]").val($(this).find("option:selected").attr("data-iso")); 
			$("input[name=idRegion]").val($(this).val()); 
			$("input[name=regionName]").val($(this).find("option:selected").attr("data-name")); 
			$("input[name=idSubRegion]").val(""); 
			$("input[name=subRegionName]").val(""); 
		});
		$(".addSelectSubRegion").on("change",".selectSubRegion",function() {
			$("input[name=idSubRegion]").val($(this).val()); 
			$("input[name=subRegionName]").val($(this).find("option:selected").attr("data-name")); 
		});
		function getRegions(){
			$(".addSelectRegion").append("<div class=\"loading\"><small><i class=\"fa fa-spinner fa-spin\"></i> đang tải quốc gia, vui lòng chờ...</small></div>"); 
			$.ajax({
				url: "'.route("get.json",array(config('app.url'),'region','all')).'",
				type: "GET",
				dataType: "json",
				cache: false,
				success: function (result) {
					$(".addSelectRegion .loading").empty(); 
					if(result.success==true){
						getSubregion($("input[name=idRegion]").val()); 
						$(".addSelectRegion").append("<div class=\"input-group\"><select class=\"selectRegion\" data-placeholder=\"Chọn quốc gia...\" name=\"channelRegion\" required>"
						+"<option value=\"\"></option></select></div>"); 
						$.each(result.region, function(i, item) {
							if(item._id.$oid==$("input[name=idRegion]").val()){
								$(".addSelectRegion .selectRegion").append("<option value="+item._id.$oid+" data-icon=\"flag-"+item.iso.toLowerCase()+"\"  data-iso="+item.iso.toLowerCase()+" data-name="+item.title+" selected>"+item.title+"</option>");
							}else{
								$(".addSelectRegion .selectRegion").append("<option value="+item._id.$oid+"  data-icon=\"flag-"+item.iso.toLowerCase()+"\"  data-iso="+item.iso.toLowerCase()+" data-name="+item.title+">"+item.title+"</option>");
							}
						}); 
						function format(icon) {
							var originalOption = icon.element;
							return "<i class=\"flag " + $(originalOption).data("icon") + "\"></i> " + icon.text;
						}
						jQuery(".addSelectRegion .selectRegion").select2({
							width: "100%",
							formatResult: format
						});
					}else{
						
					}
				}
			});
		} 
		function getSubregion(idRegion){
			$(".addSelectSubRegion").empty(); 
			$(".addSelectSubRegion").append("<div class=\"loading\"><small><i class=\"fa fa-spinner fa-spin\"></i> đang tải thành phố, vui lòng chờ...</small></div>"); 
			$.ajax({
				url: "https://'.config('app.url').'/json/subregion/"+idRegion,
				type: "GET",
				dataType:"json",
				cache: false,
				success: function (result) {
					$(".addSelectSubRegion .loading").empty(); 
					$(".addSelectRegion .input-group-addon").html("<i class=\"flag flag-"+$("input[name=regionIso]").val()+"\"></i>"); 
					if(result.success==true){
						$(".addSelectSubRegion").append("<select class=\"selectSubRegion\" data-placeholder=\"Chọn thành phố...\" name=\"channelSubRegion\">"
						+"<option value=\"\"></option></select>"); 
						$.each(result.subregion, function(i, item) {
							if(item._id.$oid==$("input[name=idSubRegion]").val()){
								$(".addSelectSubRegion .selectSubRegion").append("<option value="+item._id.$oid+" data-name="+item.title+" selected>"+item.title+"</option>");
							}else{
								$(".addSelectSubRegion .selectSubRegion").append("<option value="+item._id.$oid+" data-name="+item.title+">"+item.title+"</option>");
							}
						}); 
						function format(icon) {
							var originalOption = icon.element;
							return "<i class=\"fa fa-map-marker\"></i> " + icon.text;
						}
						jQuery(".addSelectSubRegion .selectSubRegion").select2({
							width: "100%",
							formatResult: format
						});
					}else{
						$(".addSelectSubRegion").empty(); 
					}
				}
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
		$("#summernote").summernote({
			placeholder: "Bạn đang cung cấp gì?",
			tabsize: 2,
			height: 100, 
			minHeight: 350, 
			maxHeight: 500,
			focus: true, 
			toolbar: [
				["style", ["bold", "italic", "underline", "clear"]],
				["fontsize", ["fontsize"]],
				["color", ["color"]],
				["para", ["ul", "ol", "paragraph"]],
				["table", ["table","codeview"]],
				["insert", ["link", "picture","video"]]
			],callbacks: {
				onKeydown: function(e) {
					var limiteCaracteres = 5000;
					var caracteres = $(".note-editable").text();
					var totalCaracteres = caracteres.length;
					if(totalCaracteres >= limiteCaracteres){
						
					}else{
						var textTotal="<span>Tối đa: <\/span>"+totalCaracteres+"/"+limiteCaracteres+" ký tự";
						$("#maxContentPost").html(textTotal);
					}     
				}
			}
		  }); 
		var $validator = jQuery("#postForm").validate({
			highlight: function(element) {
			  jQuery(element).closest(".form-group").removeClass("has-success").addClass("has-error");
			},
			success: function(element) {
			  jQuery(element).closest(".form-group").removeClass("has-error");
			}
		});
		$("#savePost").on("click", function () {
			if($("input[name=price]").length){
				var $validator = jQuery("#postForm").validate({
					rules: {
						price: {
						  required: true,
						  digits: true
						}
					}
				});
			}
			var $valid = jQuery("#postForm").valid();
			if(!$valid) {
				$validator.focusInvalid();
				return false;
			}else{
				$("#postForm").css("position", "relative"); 
				$("#postForm").append("<div id=\"preloaderInBox\"><div id=\"status\"><i class=\"fa fa-spinner fa-spin\"></i></div></div>"); 
				var arr = [];
				$(".fileMedia .imgItemInsert").each(function() {
					if($(this).attr("data-type")=="image"){
						var fileTmp=$(this).attr("data-file"); 
						var mediaIdRandom=$(this).attr("data-mediaIdRandom"); 
						var destinationPath=$(this).attr("data-destinationPath"); 
						var item={"type":"image","fileTmp":fileTmp,"mediaIdRandom":mediaIdRandom,"destinationPath":destinationPath}; 
						arr.push(item);
					}else if($(this).attr("data-type")=="video"){
						var fileTmp=$(this).attr("data-file"); 
						var mediaIdRandom=$(this).attr("data-mediaIdRandom"); 
						var destinationPath=$(this).attr("data-destinationPath"); 
						var item={"type":"video","fileTmp":fileTmp,"mediaIdRandom":mediaIdRandom,"destinationPath":destinationPath}; 
						arr.push(item);
					}else if($(this).attr("data-type")=="files"){
						var fileTmp=$(this).attr("data-file"); 
						var destinationPath=$(this).attr("data-destinationPath"); 
						var item={"type":"files","fileTmp":fileTmp,"destinationPath":destinationPath}; 
						arr.push(item);
					}
				}); 
				var mediaJson=JSON.stringify(arr); 
				var postTitle=$("input[name=inputTitle]").val(); 
				var postContent=$("textarea[name=postContent]").val();
				var price=$("input[name=price]").val(); 
				var tags=$("select[name=\"tags[]\"]").select2("val"); 
				if($("select[name=channelId]").val()){
					var channelId=$("select[name=channelId]").val(); 
				}else{
					var channelId="empty";
				}
				var idRegion=$("input[name=idRegion]").val(); 
				var idSubRegion=$("input[name=idSubRegion]").val(); 
				var address=$("input[name=inputAddress]").val(); 
				var name=$("input[name=inputName]").val(); 
				var email=$("input[name=inputEmail]").val(); 
				var phone=$("input[name=inputPhone]").val(); 
				if($("input[name=hiddenEmail]:checked").length){
					var hiddenEmail="hidden";
				}else{
					var hiddenEmail="show";
				}
				if($("input[name=hiddenPhone]:checked").length){
					var hiddenPhone="hidden";
				}else{
					var hiddenPhone="show";
				}
				var formData = new FormData();
				formData.append("postId", $("input[name=postId]").val()); 
				formData.append("postTitle", postTitle); 
				formData.append("postContent", postContent); 
				formData.append("price", price); 
				formData.append("medias", mediaJson); 
				formData.append("tags", tags); 
				formData.append("channelId", channelId); 
				formData.append("idRegion", idRegion); 
				formData.append("regionName", $("input[name=regionName]").val()); 
				formData.append("idSubRegion", idSubRegion); 
				formData.append("subRegionName", $("input[name=subRegionName]").val()); 
				formData.append("address", address); 
				formData.append("name", name); 
				formData.append("email", email); 
				formData.append("phone", phone); 
				formData.append("hiddenEmail", hiddenEmail); 
				formData.append("hiddenPhone", hiddenPhone); 
				$.ajax({
					url: "'.route("post.add.request",config('app.url')).'",
					headers: {"X-CSRF-TOKEN": $("meta[name=_token]").attr("content")},
					type: "post",
					cache: false,
					contentType: false,
					processData: false,
					data: formData,
					dataType:"json",
					success:function(result){ 
						console.log(result); 
						if(result.success==true){
							$(window).unbind("beforeunload"); 
							window.location.href = result.link;
						}else if(result.success==false){
							$("#postForm #preloaderInBox").css("display", "none"); 
							jQuery.gritter.add({
								title: "Thông báo!",
								text: result.message, 
								class_name: "growl-danger",
								sticky: false,
								time: ""
							});
							
						}
					},
					error: function(result) {
						jQuery.gritter.add({
							title: "Thông báo!",
							text: "Không thể đăng bài, vui lòng thử lại! ", 
							class_name: "growl-danger",
							sticky: false,
							time: ""
						});
						$("#postForm #preloaderInBox").css("display", "none"); 
					}
				}); 
			}
			return false; 
		});
		$("#postForm").on("click","#postDelete",function() {
			var formData = new FormData();
			formData.append("postId", $("input[name=postId]").val()); 
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
					jQuery.gritter.add({
						title: "Xóa bài đăng thành công!",
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
		}); 
	', $dependencies);
?>