<header id="header">
	<div id="logo-group">
		<span id="logo"> <a href="{{route('index')}}"><img src="//cungcap.vn/themes/default/assets/img/logo-red-white.svg" alt="Cung Cấp"></a> </span>
	</div>
	<div class="pull-right">
		
		<div id="hide-menu" class="btn-header pull-right">
			<span> <a href="javascript:void(0);" data-action="toggleMenu" title="Collapse Menu"><i class="fa fa-reorder"></i></a> </span>
		</div>
		<div class="btn-header pull-right">
			<span> <a href="{{route('login')}}" title="Login"><i class="fa fa-user"></i></a> </span>
		</div>
		<ul id="mobile-profile-img" class="header-dropdown-list hidden-xs padding-5">
			<li class="">
				<a href="#" class="dropdown-toggle no-margin userdropdown" data-toggle="dropdown"> 
				</a>
				<ul class="dropdown-menu pull-right">
					<li>
						<a href="javascript:void(0);" class="padding-10 padding-top-0 padding-bottom-0"><i class="fa fa-cog"></i> Setting</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="profile.html" class="padding-10 padding-top-0 padding-bottom-0"> <i class="fa fa-user"></i> <u>P</u>rofile</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="javascript:void(0);" class="padding-10 padding-top-0 padding-bottom-0" data-action="toggleShortcut"><i class="fa fa-arrow-down"></i> <u>S</u>hortcut</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="javascript:void(0);" class="padding-10 padding-top-0 padding-bottom-0" data-action="launchFullscreen"><i class="fa fa-arrows-alt"></i> Full <u>S</u>creen</a>
					</li>
					<li class="divider"></li>
					<li>
						<a href="login.html" class="padding-10 padding-top-5 padding-bottom-5" data-action="userLogout"><i class="fa fa-sign-out fa-lg"></i> <strong><u>L</u>ogout</strong></a>
					</li>
				</ul>
			</li>
		</ul>
		<div id="search-mobile" class="btn-header pull-right">
			<span> <a href="javascript:void(0)" title="Search"><i class="fa fa-search"></i></a> </span>
		</div>
		<form action="search.html" class="header-search pull-right">
			<input id="search-fld"  type="text" name="param" placeholder="Tìm kiếm" data-autocomplete='[
			"ActionScript",
			"AppleScript",
			"Asp",
			"BASIC",
			"C",
			"C++",
			"Clojure",
			"COBOL",
			"ColdFusion",
			"Erlang",
			"Fortran",
			"Groovy",
			"Haskell",
			"Java",
			"JavaScript",
			"Lisp",
			"Perl",
			"PHP",
			"Python",
			"Ruby",
			"Scala",
			"Scheme"]'>
			<button type="submit">
				<i class="fa fa-search"></i>
			</button>
			<a href="javascript:void(0);" id="cancel-search-js" title="Cancel Search"><i class="fa fa-times"></i></a>
		</form>
		<div id="speech-btn" class="btn-header pull-right hidden-sm hidden-xs">
			<div> 
				<a href="javascript:void(0)" title="Voice Command" data-action="voiceCommand"><i class="fa fa-microphone"></i></a> 
				<div class="popover bottom"><div class="arrow"></div>
					<div class="popover-content">
						<h4 class="vc-title">Voice command activated <br><small>Please speak clearly into the mic</small></h4>
						<h4 class="vc-title-error text-center">
							<i class="fa fa-microphone-slash"></i> Voice command failed
							<br><small class="txt-color-red">Must <strong>"Allow"</strong> Microphone</small>
							<br><small class="txt-color-red">Must have <strong>Internet Connection</strong></small>
						</h4>
						<a href="javascript:void(0);" class="btn btn-success" onclick="commands.help()">See Commands</a> 
						<a href="javascript:void(0);" class="btn bg-color-purple txt-color-white" onclick="$('#speech-btn .popover').fadeOut(50);">Close Popup</a> 
					</div>
				</div>
			</div>
		</div>
		<ul class="header-dropdown-list hidden-xs">
			<li>
				<a href="#" class="dropdown-toggle" data-toggle="dropdown"> <i class="flag flag-us" alt="United States"></i> <span class="hidden-xs"> English (US) </span> <i class="fa fa-angle-down"></i> </a>
				<ul class="dropdown-menu pull-right">
					<li class="active">
						<a href="javascript:void(0);"><i class="flag flag-us" alt="United States"></i> English (US)</a>
					</li>
					<li>
						<a href="javascript:void(0);"><i class="flag flag-fr" alt="France"></i> Français</a>
					</li>
					<li>
						<a href="javascript:void(0);"><i class="flag flag-es" alt="Spanish"></i> Español</a>
					</li>
					<li>
						<a href="javascript:void(0);"><i class="flag flag-de" alt="German"></i> Deutsch</a>
					</li>
					<li>
						<a href="javascript:void(0);"><i class="flag flag-jp" alt="Japan"></i> 日本語</a>
					</li>
					<li>
						<a href="javascript:void(0);"><i class="flag flag-cn" alt="China"></i> 中文</a>
					</li>	
					<li>
						<a href="javascript:void(0);"><i class="flag flag-it" alt="Italy"></i> Italiano</a>
					</li>	
					<li>
						<a href="javascript:void(0);"><i class="flag flag-pt" alt="Portugal"></i> Portugal</a>
					</li>
					<li>
						<a href="javascript:void(0);"><i class="flag flag-ru" alt="Russia"></i> Русский язык</a>
					</li>
					<li>
						<a href="javascript:void(0);"><i class="flag flag-kr" alt="Korea"></i> 한국어</a>
					</li>						
					
				</ul>
			</li>
		</ul>
	</div>
</header>