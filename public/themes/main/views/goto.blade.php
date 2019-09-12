<?
	Theme::setTitle('Url Redirect'); 
	Theme::setNoindex('NOINDEX,NOFOLLOW'); 
	if($note->type=='site'){
		$link=$note->link; 
		Theme::setCanonical(route('site.show',array(config('app.url'),$note->id,str_slug($note->title, '-')))); 
	}else if($note->type=='image'){
		$jsonDecode=json_decode($note->attribute['craw_content']); 
		if(!empty($jsonDecode->ru)){
			$link=$jsonDecode->ru; 
		}else{
			$link=''; 
		}
	}else if($note->type=='domain'){
		$link='http://'.$note->domain; 
		Theme::setCanonical('http://'.$note->domain.'.d.'.config('app.url')); 
	}else if($note->type=='affiliate'){
		$link=$note->deeplink; 
		$linkResult=$note->url; 
	}
?>
<script type="application/ld-json" id="json-url">{!!json_encode($link)!!}</script>
@if($note->type=='affiliate')
	<script type="application/ld-json" id="json-url-affiliate">{!!json_encode($linkResult)!!}</script>
@else
	<script type="application/ld-json" id="json-url-affiliate">{!!json_encode($link)!!}</script>
@endif
<div class="container">
	<div class="jumbotron">
		<div class="card-body">
			<div class="form-group">
				<div class="text-center">
					<img src="//cungcap.vn/themes/main/assets/img/logo-red-blue.svg" height="50" class="d-inline-block align-top" alt="Cung Cấp">
				</div>
			</div>
			<div class="form-group">
				<h3>{!!$note->title!!}</h3>
			</div>
			<div class="form-group">
				<div class="alert alert-dark">
					This URL (<strong>@if($note->type=='affiliate')<span id="linkUrlAffiliate"></span>@else<span id="linkUrl"></span>@endif</strong>) is not belong to Cung Cấp, if you want to continue, please click bellow button to redirect to 
				</div>
			</div>
			<div class="form-group">
			<a class="btn btn-success btn-block" id="linkContinue" href=""><h2>Click here to continue <span id="timeLeft">5</span></h2></a>
			</div>
		</div>
	</div>
</div> 
<?
	$dependencies = array(); 
	Theme::asset()->writeScript('loadLazy','
		var redirUrl=jQuery.parseJSON(jQuery("#json-url").html());
		var redirUrlAffiliate=jQuery.parseJSON(jQuery("#json-url-affiliate").html());
		jQuery(document).ready(function(){
			jQuery("#linkContinue").attr("href",redirUrl);
			jQuery("#linkUrl").html(redirUrl);
			jQuery("#linkUrlAffiliate").html(redirUrlAffiliate);
		}); 
		window.setInterval(function() {
		var timeLeft    = $("#timeLeft").html();                                
			if(eval(timeLeft) == 0){
					window.location= (redirUrl);                 
			}else{              
				$("#timeLeft").html(eval(timeLeft)- eval(1));
			}
		}, 1000); 
	', $dependencies);
?>