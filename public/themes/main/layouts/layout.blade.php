<!DOCTYPE html>
<html lang="vi">

    <head>
        {!! meta_init() !!}
		<meta name="_token" content="{{ csrf_token() }}">
        <meta name="keywords" content="@get('keywords')">
        <meta name="description" content="@get('description')">
        <meta name="author" content="@get('author')"> 
		<link rel="icon" href="https://cungcap.vn/favicon.png" /> 
		@if(Theme::has('noindex'))<meta name="robots" content="{{Theme::get('noindex')}}" />@else<meta name="robots" content="index,follow" />@endif 
		@if(Theme::has('canonical')) 
		<link rel="canonical" href="{{Theme::get('canonical')}}" /> 
		<meta property="og:url" content="{{Theme::get('canonical')}}" /> 
		<meta name="twitter:url" content="{{Theme::get('canonical')}}" /> 
		<meta property="twitter:url" content="{{Theme::get('canonical')}}" /> 
		@endif 
		@if(Theme::has('type'))<meta property="og:type" content="{{Theme::get('type')}}" />@endif 
		<meta property="og:site_name" content="Cung Cấp" /> 
		<meta property="og:title" content="@get('title')" /> 
		<meta name="twitter:card" content="summary"> 
		<meta name="twitter:card" value="summary"> 
		<meta name="twitter:site" content="@conduongviet"> 
		<meta property="twitter:title" content="@get('title')" > 
		<meta name="twitter:title" content="@get('title')" >
		<meta property="og:description" content="@get('description')" /> 
		<meta property="twitter:description" content="@get('description')" > 
		<meta name="twitter:description" content="@get('description')" >
		@if(Theme::has('image')) 
		<meta property="og:image" content="{{Theme::get('image')}}" /> 
		<meta property="twitter:image" content="{{Theme::get('image')}}" > 
		<meta name="twitter:image" content="{{Theme::get('image')}}" >
		@endif 
        <title>@get('title')</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
		<style>
			body {
				margin: 0; 
				background: #3b5997;
			}

			header {
				background-color: antiquewhite;
				padding: 5px;
			}

			#main{
				background-color: cornsilk;
				padding: 10px;
			}

			footer {
				background-color: aliceblue;
				padding: 5px;
			}
			.carousel-caption{background: rgba(0, 0, 0, 0.5);}
			.carousel-control-next, .carousel-control-prev{background: rgba(0, 0, 0, 0.1);} 
			.row-pad-5 {}
			.row-pad-5 [class*="col-lg"], .row-pad-5 [class*="col-md"], .row-pad-5 [class*="col-sm"]{
				padding-left: 5px;
				padding-right: 5px;
			}
			.siteList{word-wrap:break-word;} 
			.badge{margin:0px 5px 0px 0px;}
			.postContent img{max-width:100% !important;}
			.postContent table{max-width:100% !important;}
			.autocomplete-suggestions{background:#fff;font-weight:normal;cursor:pointer;overflow:auto;margin-top:1px;border:solid 1px #dadada;}
			.autocomplete-suggestion{padding:10px 5px;font-size:1em;}
			.autocomplete-selected{background:#f0f0f0;}
			.autocomplete-suggestions strong{font-weight:normal;color:#3399ff;}
		</style> 
        @styles() 
        @get('appendHeader') 
    </head>

    <body>
		<div id="fb-root"></div>
		<script async defer crossorigin="anonymous" src="https://connect.facebook.net/vi_VN/sdk.js#xfbml=1&version=v4.0&appId=1506437109671064&autoLogAppEvents=1"></script>
        @content()
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
		<script src="https://cungcap.vn/themes/main/assets/js/jquery.autocomplete.min.js"></script>
        @scripts()
    </body>

</html>
