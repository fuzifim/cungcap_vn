<?
Theme::setTitle('Đăng ký'); 
Theme::setSearch('register'); 
Theme::setSearchDisable('disable'); 
Theme::setType('website'); 
Theme::setCanonical(route('register',array(config('app.url')))); 
?>
@partial('header') 
<style>
.error{display:block;color:red;}
.groupForm{position:relative;}
#preloader{position:fixed;top:0;left:0;width:100%;height:100%;background-color:#e4e7ea;z-index:10000;}
#status{width:30px;height:30px;position:absolute;left:50%;top:50%;margin:-15px 0 0 -15px;font-size:32px;}
#preloaderInBox{position:absolute;top:0;left:0;width:100%;height:100%;background-color:#e4e7ea;z-index:10000;opacity:0.8;}
</style>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<div class="container">
    <div class="row">
      <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
        <div class="card card-signin my-5">
          <div class="card-body">
            <h5 class="card-title text-center">Đăng ký tài khoản</h5>
            <form class="form-signin" id="formRegister" method="post"  action="{{ route('register',config('app.url')) }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
				<div class="form-group">
					<input type="text" id="inputName" name="fullName" class="form-control" placeholder="Tên đầy đủ" required autofocus>
				  </div>
              <div class="form-group">
                <input type="email" id="inputEmail" name="email" class="form-control" placeholder="Email address" required autofocus>
              </div>
				<div class="form-group">
                <input type="phone" id="inputPhone" name="phone" class="form-control" placeholder="Số điện thoại" required autofocus>
              </div>
              <div class="form-group">
                <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
              </div>
			<div class="form-group">
                <input type="password" id="inputRePassword" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu" required>
            </div>
              <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="customCheck1">
                <label class="custom-control-label" for="customCheck1">Đồng ý với các điều khoản</label>
              </div>
              <button class="btn btn-lg btn-success btn-block text-uppercase" id="btnRegister" type="submit">Đăng ký</button>
			  <hr class="my-4"> 
			  Nếu đã có tài khoản, bạn hãy <a href="{{route('login',config('app.url'))}}">đăng nhập</a>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
@partial('footer') 
<?
	Theme::asset()->themePath()->add('style-gritter', 'library/gritter/jquery.gritter.css'); 
	Theme::asset()->themePath()->add('js-gritter', 'library/gritter/jquery.gritter.min.js'); 
	Theme::asset()->themePath()->add('js-validate', 'js/jquery.validate.min.js');
	$dependencies = array(); 
	Theme::asset()->writeScript('loadLazy',' 
		$("#formRegister").on("click","#btnRegister",function() {
			var $validator = jQuery("#formRegister").validate({
				highlight: function(element) {
				  jQuery(element).closest(".form-group").removeClass("has-success").addClass("has-error");
				},
				success: function(element) {
				  jQuery(element).closest(".form-group").removeClass("has-error");
				}
			});
			var $valid = jQuery("#formRegister").valid();
			if(!$valid) {
				$validator.focusInvalid();
				return false;
			}else{
				$("#formRegister").css("position", "relative"); 
				$("#formRegister").append("<div id=\"preloaderInBox\"><div id=\"status\"><i class=\"fa fa-spinner fa-spin\"></i></div></div>"); 
				var formData = new FormData();
				formData.append("fullName",$("input[name=fullName]").val()); 
				formData.append("email",$("input[name=email]").val()); 
				formData.append("phone",$("input[name=phone]").val()); 
				formData.append("password",$("input[name=password]").val()); 
				formData.append("password_confirmation",$("input[name=password_confirmation]").val()); 
				$.ajax({
					url: "'.route("register.request",array(config('app.url'))).'",
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
							jQuery.gritter.add({
								title: "Thông báo!",
								text: result.message, 
								class_name: "growl-success",
								sticky: false,
								time: ""
							});
						}else{
							jQuery.gritter.add({
								title: "Thông báo!",
								text: result.message, 
								class_name: "growl-danger",
								sticky: false,
								time: ""
							});
							$("#formRegister #preloaderInBox").css("display", "none"); 
						}
							
					},
					error: function(result) {
						jQuery.gritter.add({
							title: "Thông báo!",
							text: "Không thể đăng ký tài khoản, vui lòng thử lại!", 
							class_name: "growl-danger",
							sticky: false,
							time: ""
						});
						$("#formRegister #preloaderInBox").css("display", "none"); 
					}
				}); 
				return false; 
			}
		}); 
	', $dependencies);
?>