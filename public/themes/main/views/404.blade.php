<?
	Theme::setTitle($keyword.': '.Request::url()); 
	//Theme::setNoindex('NOINDEX,NOFOLLOW');  
?>
@partial('header') 
<div class="container">
	<div class="mt-2">
		<div class="alert alert-warning" role="alert">
			<h4 class="alert-heading">{!!$keyword!!}</h4>
			this path <code>{{Request::url()}}</code> has been deleted or changed. Please find again!
		</div>
	</div>
</div>
@partial('footer') 