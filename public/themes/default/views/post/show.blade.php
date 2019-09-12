<?
Theme::setTitle('Post title');
?>
@partial('leftPanel')
<div id="main" role="main">
	<div id="ribbon">
		<span class="ribbon-button-alignment"> 
			<span id="refresh" class="btn btn-ribbon" data-action="resetWidgets" data-title="refresh" rel="tooltip" data-placement="bottom" data-original-title="<i class='text-warning fa fa-warning'></i> Warning! This will reset all your widget settings." data-html="true" data-reset-msg="Would you like to RESET all your saved widgets and clear LocalStorage?"><i class="fa fa-refresh"></i></span> 
		</span>
		<ol class="breadcrumb"><li>Home</li><li>Post Title</li></ol>
	</div>
	<div id="content">
		
	</div>
</div>
@partial('pageFooter')