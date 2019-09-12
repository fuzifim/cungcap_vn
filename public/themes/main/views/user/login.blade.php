<?
Theme::setTitle('Đăng nhập'); 
Theme::setSearch('Đăng nhập'); 
Theme::setSearchDisable('disable'); 
Theme::setType('website'); 
Theme::setCanonical(route('login',array(config('app.url')))); 
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
            <h5 class="card-title text-center">Đăng nhập</h5>
            <form class="form-signin" id="formLogin" method="post"  action="{{ route('login',config('app.url')) }}">
				<input type="hidden" name="_token" value="{{ csrf_token() }}">
              <div class="form-group">
                <input type="email" id="inputEmail" name="user" class="form-control" placeholder="Email address" required autofocus>
              </div>

              <div class="form-group">
                <input type="password" id="inputPassword" name="password" class="form-control" placeholder="Password" required>
              </div>

              <div class="custom-control custom-checkbox mb-3">
                <input type="checkbox" class="custom-control-input" id="customCheck1">
                <label class="custom-control-label" for="customCheck1">Remember password</label>
              </div>
              <button class="btn btn-lg btn-primary btn-block text-uppercase" id="btnLogin" type="submit">Sign in</button>
              <hr class="my-4"> 
			  Nếu chưa có tài khoản, bạn hãy <a href="{{route('register',config('app.url'))}}">đăng ký</a>
              <!--<button class="btn btn-lg btn-danger btn-block text-uppercase" type="submit"><i class="fab fa-google mr-2"></i> Sign in with Google</button>
              <button class="btn btn-lg btn-primary btn-block text-uppercase" type="submit"><i class="fab fa-facebook-f mr-2"></i> Sign in with Facebook</button>-->
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
		$("#formLogin").on("click","#btnLogin",function() {
			var $validator = jQuery("#formLogin").validate({
				highlight: function(element) {
				  jQuery(element).closest(".form-group").removeClass("has-success").addClass("has-error");
				},
				success: function(element) {
				  jQuery(element).closest(".form-group").removeClass("has-error");
				}
			});
			var $valid = jQuery("#formLogin").valid();
			if(!$valid) {
				$validator.focusInvalid();
				return false;
			}else{
				$("#formLogin").css("position", "relative"); 
				$("#formLogin").append("<div id=\"preloaderInBox\"><div id=\"status\"><i class=\"fa fa-spinner fa-spin\"></i></div></div>"); 
				var formData = new FormData();
				formData.append("user", $("input[name=user]").val()); 
				formData.append("password", $("input[name=password]").val()); 
				$.ajax({
					url: "'.route("login",array(config('app.url'))).'",
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
							window.location.href = result.link;
						}else{
							jQuery.gritter.add({
								title: "Thông báo!",
								text: result.message, 
								class_name: "growl-danger",
								sticky: false,
								time: ""
							});
							$("#formLogin #preloaderInBox").css("display", "none"); 
						}
							
					},
					error: function(result) {
						jQuery.gritter.add({
							title: "Thông báo!",
							text: "Không thể đăng nhập, vui lòng thử lại!", 
							class_name: "growl-danger",
							sticky: false,
							time: ""
						});
						$("#formLogin #preloaderInBox").css("display", "none"); 
					}
				}); 
				return false;
			} 
		}); 
	', $dependencies);
?>