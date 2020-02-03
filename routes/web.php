<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::group(['domain' => '{domain}'], function() {
	Route::get('/', array(
		'as' => 'index',
		'uses' => 'IndexController@index'));
    Route::get('/goTo/{url}', array(
        'as' => 'go.to',
        'uses' => 'PageController@goToUrl'))->where('url', '.*');
	Route::get('/category', array(
		'as' => 'category.list',
		'uses' => 'IndexController@categoryList'));
	Route::get('/video/{id}/{slug}', array(
		'as' => 'video.show',
		'uses' => 'IndexController@videoShow')); 
	Route::get('/videos', array(
		'as' => 'video.list',
		'uses' => 'IndexController@videoList')); 
	Route::get('/sites', array(
		'as' => 'site.list',
		'uses' => 'IndexController@siteList'));
	Route::get('/site/{id}/{slug}', array(
		'as' => 'site.show',
		'uses' => 'IndexController@siteShow')); 
	Route::get('/post/add', array(
		'as' => 'post.add',
		'uses' => 'PostController@add')); 
	Route::post('/post/add', array(
		'as' => 'post.add.request',
		'uses' => 'PostController@addRequest')); 
	Route::get('/post/edit/{id}', array(
		'as' => 'post.edit',
		'uses' => 'PostController@edit')); 
	Route::post('/post/delete/{type}', array(
		'as' => 'post.delete',
		'uses' => 'PostController@deleteType')); 
	Route::get('/post/me/{type}', array(
		'as' => 'post.me',
		'uses' => 'PostController@me')); 
	Route::get('/post/{id}/{slug}', array(
		'as' => 'post.show',
		'uses' => 'IndexController@postShow')); 
	Route::get('/domain', array(
		'as' => 'domain.list',
		'uses' => 'IndexController@domainList')); 
	Route::post('/domain/{slug}', array(
		'as' => 'domain.set.status',
		'uses' => 'DomainController@setStatus'));
	Route::get('/product', array(
		'as' => 'product.list',
		'uses' => 'ProductController@productList')); 
	Route::get('/product/{id}/{slug}', array(
		'as' => 'product.show',
		'uses' => 'ProductController@productShow')); 
	Route::get('/d/{domain}', function($domain){
		return redirect('http://'.$domain.'.d.'.config('app.url'),301);
	}); 
	Route::get('/com/{id}/{slug}', array(
		'as' => 'company.show',
		'uses' => 'IndexController@companyShow')); 
	Route::get('/company/', array(
		'as' => 'company.list',
		'uses' => 'IndexController@companyList')); 
	Route::get('/company/{slug}', function($domain,$slug){
		$slug=explode('-',$slug); 
		return redirect('https://'.config('app.url').'/com/'.$slug[0].'/old');
	});
	Route::get('/news/{id}/{slug}', array(
		'as' => 'news.show',
		'uses' => 'IndexController@newsShow')); 
	Route::get('/news', array(
		'as' => 'news.list',
		'uses' => 'IndexController@newsList')); 
	Route::get('/register', array(
		'as' => 'register',
		'uses' => 'UserController@register')); 
	Route::post('/register', array(
		'as' => 'register.request',
		'uses' => 'UserController@registerRequest')); 
	Route::get('/login', array(
		'as' => 'login',
		'uses' => 'UserController@login')); 
	Route::post('/login', array(
		'as' => 'login.request',
		'uses' => 'UserController@loginRequest')); 
	Route::get('/logout', array(
		'as' => 'logout',
		'uses' => 'UserController@logout')); 
	Route::get('/sitemap{type}', array(
		'as' => 'sitemap',
		'uses' => 'IndexController@sitemap')); 
	Route::get('/rss{type}', array(
		'as' => 'rss',
		'uses' => 'IndexController@rss')); 
	Route::get('/sitelink/{slug}', function($domain,$slug){
		return redirect('http://'.$slug.'.d.'.config('app.url'),301);
	}); 
	Route::get('/channel/add', array(
		'as' => 'channel.add',
		'uses' => 'ChannelController@channelAdd')); 
	Route::post('/channel/add', array(
		'as' => 'channel.add.request',
		'uses' => 'ChannelController@channelAddRequest')); 
	Route::get('/channel/me/{type}', array(
		'as' => 'channel.me',
		'uses' => 'ChannelController@me')); 
	Route::get('/channel', array(
		'as' => 'channel.list',
		'uses' => 'ChannelController@channelList')); 
	Route::post('/media/{type}', array(
		'as' => 'media.type',
		'uses' => 'MediaController@mediaType')); 
	Route::get('/@{slug}', array(
		'as' => 'profile',
		'uses' => 'UserController@profile')); 
	Route::get('/ip', array(
		'as' => 'ip.list',
		'uses' => 'IpController@ipList')); 
	Route::get('/ip/{ip}', array(
		'as' => 'ip.show',
		'uses' => 'IpController@show')); 
	Route::get('/craw/{keyword}', array(
		'as' => 'craw',
		'uses' => 'IndexController@craw')); 
	Route::get('/test', array(
		'as' => 'test',
		'uses' => 'TestController@test')); 
	Route::get('/cron/{type}', array(
		'as' => 'cron',
		'uses' => 'CronController@index')); 
	Route::get('/json/{type}/{id}', array(
		'as' => 'get.json',
		'uses' => 'JsonController@index')); 
	Route::get('/search/{type}', array(
		'as' => 'search.type',
		'uses' => 'SearchController@searchType')); 
	Route::get('/go/{id}/{slug}', array(
		'as' => 'go.to',
		'uses' => 'IndexController@goTo')); 
	Route::get('/{slug}', array(
		'as' => 'show.category',
		'uses' => 'IndexController@showCategory'))->where('slug', '.*'); 
});