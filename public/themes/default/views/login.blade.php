<?
Theme::setTitle('Đăng nhập');
?>
@partial('leftPanel')

<div id="main" role="main">
	<div id="ribbon">
		<span class="ribbon-button-alignment"> 
			<span id="refresh" class="btn btn-ribbon" data-action="resetWidgets" data-title="refresh" rel="tooltip" data-placement="bottom" data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset all your widget settings." data-html="true" data-reset-msg="Would you like to RESET all your saved widgets and clear LocalStorage?"><i class="fa fa-refresh"></i></span> 
		</span>
		<ol class="breadcrumb"><li>Home</li><li>Login</li></ol>
	</div>
	<div id="content">
		<div class="well no-padding">
			<form action="" id="login-form" class="smart-form client-form" novalidate="novalidate">
				<header>
					Đăng nhập
				</header>

				<fieldset>
					
					<section>
						<label class="label">E-mail hoặc số điện thoại</label>
						<label class="input"> <i class="icon-append fa fa-user"></i>
							<input type="email" name="email">
							<b class="tooltip tooltip-top-right"><i class="fa fa-user txt-color-teal"></i> Nhập địa chỉ email hoặc số điện thoại của bạn</b></label>
					</section>

					<section>
						<label class="label">Mật khẩu</label>
						<label class="input"> <i class="icon-append fa fa-lock"></i>
							<input type="password" name="password">
							<b class="tooltip tooltip-top-right"><i class="fa fa-lock txt-color-teal"></i> Enter your password</b> </label>
						<div class="note">
							<a href="#"><i class="glyphicon glyphicon-lock"></i> Quên mật khẩu?</a> | <a href="#"><i class="glyphicon glyphicon-ok-sign"></i> Đăng ký</a>
						</div>
					</section>
					<div class="form-group">
						<a href="#" class="btn btn-xs btn-primary"><span class="fa fa-facebook"></span>  <span class=""> Với Facebook</span></a> 
						<a href="#" class="btn btn-xs btn-danger"><span class="fa fa-google"></span> <span class="">Với Google</span></a>
					</div>
				</fieldset>
				<footer>
					<button type="submit" class="btn btn-primary">
						Đăng nhập
					</button>
				</footer>
			</form>

		</div>
	</div>

</div>
@partial('pageFooter')