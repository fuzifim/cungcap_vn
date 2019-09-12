<?php echo '<?xml version="1.0" encoding="utf-8"?>'; ?>
<rss version="2.0">
<channel>
<title>Cung Cấp</title><link>https://cungcap.net/rss</link><description>Cung Cấp sản phẩm, dịch vụ kinh doanh doanh đến mọi người ⭐ ⭐ ⭐ ⭐ ⭐</description><copyright>Copyright (C) Cung Cấp</copyright><lastBuildDate>{!!\Carbon\Carbon::now()!!}</lastBuildDate><generator>Cung Cấp</generator><image><url>https://cungcap.vn/themes/main/assets/img/logo-red-blue.svg</url><title>Cung Cấp. Net</title><link>https://cungcap.net</link></image>
@foreach($getNote as $note)
@if($note['type']=="video")
<item>
	<title>Video: {{$note['title']}}</title>
	<link>{{route('video.show',array(config('app.url'),$note['_id'],str_slug($note['title'], '-')))}}</link>
	<pubDate>{{date('Y-m-d\TH:i:sP', strtotime($note['created_at']))}}</pubDate>
	<description><![CDATA[@if(!empty($note['image']))<a href="{{route('video.show',array(config('app.url'),$note['_id'],str_slug($note['title'], '-')))}}"><img width=130 height=100 src="https:{{$note['image']}}" ></a></br>@endif @if(!empty($note['description'])){{$note['description']}}@endif]]></description>
	@if(!empty($note['image']))<enclosure type="image/jpeg" url="https:{{$note['image']}}"/>@endif
</item>
@elseif($note['type']=="category")
<?
$noteImage=''; 
if(!empty($note['image'])){ 
	$noteImage=$note['image']; 
}
?>
<item>
	<title>{{$note['title']}}</title>
	<link>{{route('show.category',array(config('app.url'),str_replace(' ','+',$note['title'])))}}</link>
	<pubDate>{{date('Y-m-d\TH:i:sP', strtotime($note['created_at']))}}</pubDate>
	<description><![CDATA[@if(!empty($noteImage))<a href="{{route('show.category',array(config('app.url'),str_replace(' ','+',$note['title'])))}}"><img width=130 height=100 src="{{$noteImage}}" ></a></br>@endif @if(!empty($note['description'])){{$note['description']}}@endif]]></description>
	@if(!empty($noteImage))<enclosure type="image/jpeg" url="{{$noteImage}}"/>@endif
</item>
@endif
@endforeach
</channel></rss>