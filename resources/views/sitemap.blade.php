<?php echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset
      xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
      xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
      xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9
            http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">
<!-- https://{{config('app.url')}} -->

    <url>
        <loc>https://{{config('app.url')}}</loc>
    </url>
@if($sitemapIndex=='false')
	@if(count($getNote))
	@foreach($getNote as $note) 
	@if($note['type']=='category')
    <url>
        <loc>{{route('show.category',array(config('app.url'),str_replace(' ','+',$note['title'])))}}</loc> 
    </url> 
	@elseif($note['type']=='video') 
	<url>
		<loc>{{route('video.show',array(config('app.url'),$note['_id'],str_slug($note['title'], '-')))}}</loc> 
	</url> 
	@elseif($note['type']=='site') 
	<url>
		<loc>{{route('site.show',array(config('app.url'),$note['_id'],str_slug($note['title'], '-')))}}</loc> 
	</url> 
	@elseif($note['type']=='domain') 
	<url>
		<loc>http://{{$note['domain']}}.d.{{config('app.url')}}</loc> 
	</url> 
	@elseif($note['type']=='news') 
	<url>
		<loc>{{route('news.show',array(config('app.url'),$note['_id'],str_slug($note['title'], '-')))}}</loc> 
	</url> 
	@elseif($note['type']=='company') 
	<url>
		<loc>{{route('company.show',array(config('app.url'),$note['_id'],str_slug($note['title'], '-')))}}</loc> 
	</url> 
	@elseif($note['type']=='affiliate') 
	<url>
		<loc>{{route('product.show',array(config('app.url'),$note['_id'],str_slug($note['title'], '-')))}}</loc> 
	</url> 
	@endif
	@endforeach
	@endif 
@else 
	<url>
        <loc>https://{{config('app.url')}}/sitemap_category.xml</loc>
    </url> 
	<url>
        <loc>https://{{config('app.url')}}/sitemap_video.xml</loc>
    </url> 
	<url>
        <loc>https://{{config('app.url')}}/sitemap_domain.xml</loc>
    </url> 
	<url>
        <loc>https://{{config('app.url')}}/sitemap_company.xml</loc>
    </url> 
	<url>
        <loc>https://{{config('app.url')}}/sitemap_news.xml</loc>
    </url> 
@endif
</urlset>