<?php

namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Str;
use App\Model\Note; 
use Request; 
use Theme; 
use Auth; 
use Imagick; 
use File; 
use Cache; 
use Storage;
use Carbon\Carbon;
class MediaController extends ConstructController
{
	public function __construct(){
		parent::__construct(); 
	}
	public function mediaType(){
		if($this->_parame['type']=='uploadToTmp'){
			$fileupload = Input::file('file'); 
			$itemId = Input::get('itemId'); 
			$mime = $fileupload->getMimeType();
			$file_size = $fileupload->getSize(); 
			$fileToMb=$file_size/ 1024 / 1024; 
			if($fileToMb>2){
				return response()->json(['success'=>false,
					'message'=>'Dung lượng hình ảnh tối đa 2MB, bạn cần phải nén hình ảnh trước khi tải lên! ',
				]);
			}
			$name=preg_replace('/\..+$/', '', $fileupload->getClientOriginalName()); 
			$random=$this->keyRandom(); 
			$randomKey=Str::slug($name).'-'.$random; 
			$filename=Str::slug($name).'-'.$random.".".$fileupload->getClientOriginalExtension(); 
			$dateFolder=[
				'day'=>date('d', strtotime(Carbon::now()->format('Y-m-d H:i:s'))), 
				'month'=>date('m', strtotime(Carbon::now()->format('Y-m-d H:i:s'))), 
				'year'=>date('Y', strtotime(Carbon::now()->format('Y-m-d H:i:s')))
			]; 
			$pathFiles = public_path(). '/media/tmp/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day'];  
			if(!File::exists($pathFiles)) {
				File::makeDirectory($pathFiles, $mode = 0777, true, true); 
			}
			if(!File::exists($pathFiles.'/small')) {
				File::makeDirectory($pathFiles.'/small', $mode = 0777, true, true); 
			}
			if(!File::exists($pathFiles.'/video')) {
				File::makeDirectory($pathFiles.'/video', $mode = 0777, true, true); 
			}
			$destinationPath='media/tmp/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day'].'/'; 
			if ($mime == "image/jpeg" || $mime == "image/jpg" || $mime == "image/png" || $mime == "image/gif") {
				$fileupload->move($destinationPath,$filename); 
				$file_path = $destinationPath.$filename;
				$demention = getimagesize($file_path);
				$widthSm=320; $heightSm=214; 
				$im = new Imagick($file_path);
				if($mime == "image/gif"){
					$im = $im->coalesceImages(); 
					if($demention[0] >1280){
						foreach ($im as $frame) { 
							$frame->resizeImage( 1280 , null , Imagick::FILTER_LANCZOS, 1, TRUE);
						} 
					}else{
						foreach ($im as $frame) { 
							$w = $frame->getImageWidth();
							$h = $frame->getImageHeight();
							$frame->resizeImage($w,$h, Imagick::FILTER_LANCZOS, 1, TRUE);
						}
					}
					$im = $im->deconstructImages(); 
				}else{
					if($demention[0] >1280){
						$im->resizeImage(1280, null, Imagick::FILTER_LANCZOS, 1);
					}
					$im->setImageCompression(Imagick::COMPRESSION_JPEG);
					$im->setImageCompressionQuality(80); 
					$im->setImageFormat("jpg");
					$im->stripImage();
					$im->writeImage();
				}
				$imgSmall=new Imagick($file_path); 
				if($mime == "image/gif"){
					$imgSmall = $imgSmall->coalesceImages(); 
					foreach ($imgSmall as $frame) { 
						$frame->scaleImage($widthSm,$heightSm,true); 
						$frame->setImageBackgroundColor('white');
						$w = $frame->getImageWidth();
						$h = $frame->getImageHeight();
						$frame->extentImage($widthSm,$heightSm,($w-$widthSm)/2,($h-$heightSm)/2); 
					} 
					$imgSmall = $imgSmall->deconstructImages(); 
				}else{
					$imgSmall->scaleImage($widthSm,$heightSm,true); 
					$imgSmall->setImageBackgroundColor('white');
					$w = $imgSmall->getImageWidth();
					$h = $imgSmall->getImageHeight();
					$imgSmall->extentImage($widthSm,$heightSm,($w-$widthSm)/2,($h-$heightSm)/2); 
					$imgSmall->setImageCompression(Imagick::COMPRESSION_JPEG);
					$imgSmall->setImageCompressionQuality(85); 
					$imgSmall->setImageFormat("jpg");
					$imgSmall->stripImage();
				}
				$imgSmall->writeImages($destinationPath.'small/'.$filename,true); 
				return response()->json(['success'=>true,
					'message'=>'Upload thành công!', 
					'itemId'=>$itemId,
					'mimeType'=>'image',
					'destinationPath'=>$destinationPath, 
					'file_tmp'=>$filename, 
					'url'=>'//'.config('app.url').'/'.$file_path,
					'url_small'=>'//'.config('app.url').'/'.$destinationPath.'small/'.$filename, 
				]);
			}else if ($mime == "video/x-flv" || $mime == "video/mp4" || $mime == "application/x-mpegURL" || $mime == "video/MP2T" || $mime == "video/3gpp" || $mime == "video/quicktime" || $mime == "video/x-quicktime" || $mime == "image/mov" || $mime == "video/avi" || $mime == "video/x-msvideo" || $mime == "video/x-ms-wmv" || $mime == "video/x-matroska") {
				$fileupload->move($destinationPath,$filename); 
				$file_path = $destinationPath.$filename;
				$filename=$randomKey.'.mp4'; 
				$lowBitrateFormat = (new X264)->setKiloBitrate(200);
				FFMpeg::fromDisk('local')
					->open($file_path)
					/*->addFilter(function ($filters) {
						$filters->resize(new Dimension(640, 480));
					})*/
					->export()
					->toDisk('local')
					->inFormat($lowBitrateFormat)
					->save($destinationPath.'video/'.$filename); 
				if(file_exists($destinationPath.'video/'.$filename)){
					$media=FFMpeg::fromDisk('local')
						->open($destinationPath.'video/'.$filename)
						->getFrameFromSeconds(2)
						->export()
						->toDisk('local')
						->save($destinationPath.'video/'.$randomKey.'.png'); 
					$img=new Imagick($destinationPath.'video/'.$randomKey.'.png'); 
					$d = $img->getImageGeometry(); 
					if($d['width'] >1280){
						$img->resizeImage(1280, null, Imagick::FILTER_LANCZOS, 1);
					}
					$widthMd=720; $heightMd=480; 
					$img->scaleImage($widthMd,$heightMd,true); 
					$img->setImageBackgroundColor('white');
					$w = $img->getImageWidth();
					$h = $img->getImageHeight();
					$img->extentImage($widthMd,$heightMd,($w-$widthMd)/2,($h-$heightMd)/2);
					$img->writeImages($destinationPath.'video/'.$randomKey.'.png',true);
					
					File::delete($file_path); 
					FFMpeg::cleanupTemporaryFiles();
					return response()->json(['success'=>true,
						'message'=>'Upload thành công!', 
						'itemId'=>$itemId,
						'mimeType'=>'video', 
						'destinationPath'=>$destinationPath, 
						'media_id_random'=>$randomKey, 
						'file_tmp'=>$filename, 
						'url'=>'//videos.'.config('app.url').'/'.$destinationPath.'video/'.$filename,
					]);
				}else{
					return response()->json(['success'=>false,
						'message'=>'Lỗi không thể upload video!', 
						'itemId'=>$itemId,
					]);
				}
			}else if ($mime == "application/x-rar" || $mime == "application/x-compressed" || $mime == "application/x-zip-compressed" || $mime == "application/zip" || $mime == "multipart/x-zip" || $mime == "application/pdf" || $mime == "application/msword" || $mime == "application/vnd.openxmlformats-officedocument.wordprocessingml.document" || $mime == "application/vnd.ms-excel" || $mime == "application/vnd.ms-powerpoint" || $mime == "application/vnd.ms-powerpoint" || $mime == "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet") {
				$fileupload->move($destinationPath,$filename); 
				$file_path = $destinationPath.$filename;
				return response()->json(['success'=>true,
					'message'=>'Upload thành công!', 
					'itemId'=>$itemId,
					'mimeType'=>'file', 
					'destinationPath'=>$destinationPath, 
					'media_id_random'=>$randomKey, 
					'path'=>$file_path, 
					'file_tmp'=>$filename, 
					'url'=>'//file.'.config('app.url').'/'.$file_path,
				]);
			}else{
				return response()->json(['success'=>false,
					'message'=>'Định dạng file upload không hỗ trợ. '.$mime, 
				]);
			}
		}else if($this->_parame['type']=='delTmp'){
			$fileTmp=Input::get('fileTmp'); 
			$fileTmpSmall=Input::get('fileTmpSmall'); 
			$fileType=Input::get('fileType');
			$mediaIdRandom=Input::get('mediaIdRandom');
			$destinationPath=Input::get('destinationPath'); 
			if($fileType=='image'){
				File::delete($destinationPath.$fileTmp); 
				File::delete($destinationPath.'small/'.$fileTmp);
			}else if($fileType=='video'){
				File::delete($destinationPath.'video/'.$fileTmp); 
				File::delete($destinationPath.'video/'.$mediaIdRandom.'.png');
			}else if($fileType=='file'){
				File::delete($destinationPath.$fileTmp); 
			}
			return response()->json(['success'=>true,
				'message'=>'Đã xóa file!', 
			]);
		}else if($this->_parame['type']=='delMediaPost'){
			$postId=Input::get('postId'); 
			$mediaId=Input::get('mediaId'); 
			$mediaUrl=Input::get('mediaUrl'); 
			if(!empty($postId)){
				$note=Note::find($postId); 
				if(!empty($note->media) && count($note->media)){
					$mediaArray=$note->media; 
					$listFile=$mediaArray[$mediaId]; 
					$s3=Storage::disk('s3'); 
					if($s3->exists(str_replace("//img.cungcap.net/","",$listFile['url']))) {
						$s3->delete(str_replace("//img.cungcap.net/","",$listFile['url']));
					}
					if($s3->exists(str_replace("//img.cungcap.net/","",$listFile['url_thumb']))) {
						$s3->delete(str_replace("//img.cungcap.net/","",$listFile['url_thumb']));
					}
					if($s3->exists(str_replace("//img.cungcap.net/","",$listFile['url_small']))) {
						$s3->delete(str_replace("//img.cungcap.net/","",$listFile['url_small']));
					}
					if($s3->exists(str_replace("//img.cungcap.net/","",$listFile['url_xs']))) {
						$s3->delete(str_replace("//img.cungcap.net/","",$listFile['url_xs']));
					}
					unset($mediaArray[$mediaId]);
					$note->media=$mediaArray; 
					$note->save(); 
					return response()->json(['success'=>true,
						'message'=>$listFile['url'], 
					]);
				}
			}
		}
	}
	public function uploadFileFromTmp($mediaType,$fileTmp,$mediaIdRandom,$destinationPathTmp){
		if($mediaType=='image'){
			$file_path=$destinationPathTmp.$fileTmp; 
			if(file_exists($file_path)){
				$dateFolder=$this->makeDir(); 
				$destinationPath='media/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day'].'/'; 
				$widthMd=720; $heightMd=480; 
				$widthSm=480; $heightSm=321; 
				$widthXs=320; $heightXs=214; 
				$scale=true; 
				$img=new Imagick($file_path);  
				$filename = time().'-'.$this->keyRandom().'.'.mb_strtolower($img->getImageFormat());
				$img->writeImages($destinationPath.$filename,true); 
				$identifyImage=$img->identifyImage(); 
				$mime=$identifyImage['mimetype']; 
				$imgThumbnail=new Imagick($file_path); 
				if($mime == "image/gif"){
					$imgThumbnail = $imgThumbnail->coalesceImages(); 
					foreach ($imgThumbnail as $frame) { 
						$frame->scaleImage($widthMd,$heightMd,true); 
						$frame->setImageBackgroundColor('white');
						$w = $frame->getImageWidth();
						$h = $frame->getImageHeight();
						$frame->extentImage($widthMd,$heightMd,($w-$widthMd)/2,($h-$heightMd)/2);
					} 
					$imgThumbnail = $imgThumbnail->deconstructImages(); 
				}else{
					$imgThumbnail->scaleImage($widthMd,$heightMd,true); 
					$imgThumbnail->setImageBackgroundColor('white');
					$w = $imgThumbnail->getImageWidth();
					$h = $imgThumbnail->getImageHeight();
					$imgThumbnail->extentImage($widthMd,$heightMd,($w-$widthMd)/2,($h-$heightMd)/2);
				}
				$imgThumbnail->writeImages($destinationPath.'thumb/'.$filename,true); 
				
				$imgSmall=new Imagick($file_path); 
				if($mime == "image/gif"){
					$imgSmall = $imgSmall->coalesceImages(); 
					foreach ($imgSmall as $frame) { 
						$frame->scaleImage($widthSm,$heightSm,true); 
						$frame->setImageBackgroundColor('white');
						$w = $frame->getImageWidth();
						$h = $frame->getImageHeight();
						$frame->extentImage($widthSm,$heightSm,($w-$widthSm)/2,($h-$heightSm)/2); 
					} 
					$imgSmall = $imgSmall->deconstructImages(); 
				}else{
					$imgSmall->scaleImage($widthSm,$heightSm,true); 
					$imgSmall->setImageBackgroundColor('white');
					$w = $imgSmall->getImageWidth();
					$h = $imgSmall->getImageHeight();
					$imgSmall->extentImage($widthSm,$heightSm,($w-$widthSm)/2,($h-$heightSm)/2); 
				}
				$imgSmall->writeImages($destinationPath.'small/'.$filename,true); 
				
				$imgXS=new Imagick($file_path); 
				if($mime == "image/gif"){
					$imgXS = $imgXS->coalesceImages(); 
					foreach ($imgXS as $frame) { 
						$frame->scaleImage($widthXs,$heightXs,true); 
						$frame->setImageBackgroundColor('white');
						$w = $frame->getImageWidth();
						$h = $frame->getImageHeight();
						$frame->extentImage($widthXs,$heightXs,($w-$widthXs)/2,($h-$heightXs)/2);  
					} 
					$imgXS = $imgXS->deconstructImages(); 
				}else{
					$imgXS->scaleImage($widthXs,$heightXs,true); 
					$imgXS->setImageBackgroundColor('white');
					$w = $imgXS->getImageWidth();
					$h = $imgXS->getImageHeight();
					$imgXS->extentImage($widthXs,$heightXs,($w-$widthXs)/2,($h-$heightXs)/2); 
				}
				$imgXS->writeImages($destinationPath.'xs/'.$filename,true);
				$url_file = '//img.cungcap.net/'.$destinationPath.$filename;//change this URL 
				$url_file_thumb = '//img.cungcap.net/'.$destinationPath.'thumb/'.$filename;//change this URL 
				$url_file_small = '//img.cungcap.net/'.$destinationPath.'small/'.$filename;//change this URL 
				$url_file_xs = '//img.cungcap.net/'.$destinationPath.'xs/'.$filename;//change this URL  
				$s3=Storage::disk('s3'); 
				$s3->put($destinationPath.$filename, file_get_contents($destinationPath.$filename)); 
				$s3->put($destinationPath.'thumb/'.$filename, file_get_contents($destinationPath.'thumb/'.$filename)); 
				$s3->put($destinationPath.'small/'.$filename, file_get_contents($destinationPath.'small/'.$filename)); 
				$s3->put($destinationPath.'xs/'.$filename, file_get_contents($destinationPath.'xs/'.$filename)); 
				File::delete($destinationPath.$filename); 
				File::delete($destinationPath.'thumb/'.$filename); 
				File::delete($destinationPath.'small/'.$filename); 
				File::delete($destinationPath.'xs/'.$filename); 
				File::delete($destinationPathTmp.$fileTmp); 
				File::delete($destinationPathTmp.'small/'.$fileTmp); 
				return array(
					'type'=>'image',
					'url'=>$url_file, 
					'url_thumb'=>$url_file_thumb, 
					'url_small'=>$url_file_small, 
					'url_xs'=>$url_file_xs
				); 
			}else{
				return false; 
			}
		}else if($mediaType=='video'){
			$file_path=$destinationPathTmp.'video/'.$fileTmp; 
			if(file_exists($file_path)){
				$dateFolder=$this->makeDir(); 
				$destinationPath='media/videos/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day'].'/'; 
				File::move($file_path,$destinationPath.$fileTmp); 
				if(file_exists($destinationPath.$fileTmp)){
					$media=FFMpeg::fromDisk('local')
						->open($destinationPath.$fileTmp)
						->getFrameFromSeconds(2)
						->export()
						->toDisk('local')
						->save($destinationPath.'thumb/'.$mediaIdRandom.'.png'); 
					$img=new Imagick($destinationPath.'thumb/'.$mediaIdRandom.'.png'); 
					$d = $img->getImageGeometry(); 
					if($d['width'] >1280){
						$img->resizeImage(1280, null, Imagick::FILTER_LANCZOS, 1);
					}
					$widthMd=720; $heightMd=480; 
					$widthSm=480; $heightSm=321; 
					$widthXs=320; $heightXs=214; 
					$img->scaleImage($widthMd,$heightMd,true); 
					$img->setImageBackgroundColor('white');
					$w = $img->getImageWidth();
					$h = $img->getImageHeight();
					$img->extentImage($widthMd,$heightMd,($w-$widthMd)/2,($h-$heightMd)/2); 
					$img->setImageCompression(Imagick::COMPRESSION_JPEG);
					$img->setImageCompressionQuality(85); 
					$img->setImageFormat("jpg");
					$imgSmall->stripImage();
					$img->writeImages($destinationPath.'thumb/'.$mediaIdRandom.'.'.mb_strtolower($img->getImageFormat()),true);
					
					$imgSmall=new Imagick($destinationPath.'thumb/'.$mediaIdRandom.'.png'); 
					$imgSmall->scaleImage($widthSm,$heightSm,true); 
					$imgSmall->setImageBackgroundColor('white');
					$w = $imgSmall->getImageWidth();
					$h = $imgSmall->getImageHeight();
					$imgSmall->extentImage($widthSm,$heightSm,($w-$widthSm)/2,($h-$heightSm)/2); 
					$imgSmall->setImageCompression(Imagick::COMPRESSION_JPEG);
					$imgSmall->setImageCompressionQuality(85); 
					$imgSmall->setImageFormat("jpg");
					$imgSmall->stripImage();
					$imgSmall->writeImages($destinationPath.'small/'.$mediaIdRandom.'.'.mb_strtolower($imgSmall->getImageFormat()),true);
					
					$imgXs=new Imagick($destinationPath.'thumb/'.$mediaIdRandom.'.png'); 
					$imgXs->scaleImage($widthXs,$heightXs,true); 
					$imgXs->setImageBackgroundColor('white');
					$w = $imgXs->getImageWidth();
					$h = $imgXs->getImageHeight();
					$imgXs->extentImage($widthXs,$heightXs,($w-$widthXs)/2,($h-$heightXs)/2); 
					$imgXs->setImageCompression(Imagick::COMPRESSION_JPEG);
					$imgXs->setImageCompressionQuality(85); 
					$imgXs->setImageFormat("jpg");
					$imgXs->stripImage();
					$imgXs->writeImages($destinationPath.'xs/'.$mediaIdRandom.'.'.mb_strtolower($imgXs->getImageFormat()),true); 
					
					$url_file = '//videos.'.config('app.url').'/'.$destinationPath.$fileTmp;
					$data_file_insert = array(
						'member_id' => Auth::user()->id,
						'media_name' => $fileTmp, 
						'media_id_random'=>$mediaIdRandom, 
						'media_content'=>$content, 
						'media_path' => $destinationPath,
						'media_url' 	=> $url_file, 
						'media_url_thumb' 	=> '',
						'media_url_small' 	=> '',
						'media_url_xs' 	=> '',
						'media_size' => filesize($destinationPath.$fileTmp), 
						'media_type' =>'video/mp4', 
						'created_at'=>Carbon::now()->format('Y-m-d H:i:s'), 
						'media_storage'=>'video'
					);
					$id_media=Media::insertGetId($data_file_insert); 
					if($id_media){
						Media_join_channel::insertGetId(array(
							'media_id'=>$id_media, 
							'channel_id'=>$channelId
						)); 
						$getPost=Posts::find($postId); 
						if(!empty($getPost->id)){
							Media_join_post::insertGetId(array(
								'post_id'=>$postId, 
								'media_id'=>$id_media
							)); 
						}
						File::delete($destinationPathTmp.'video/'.$mediaIdRandom.'.png');
						$getMedia=Media::find($id_media); 
						return $getMedia; 
					}
				}
			}else{
				return false; 
			}
		}else if($mediaType=='file'){
			$file_path=$destinationPathTmp.$fileTmp; 
			if(file_exists($file_path)){
				$dateFolder=$this->makeDir(); 
				$destinationPath='media/files/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day'].'/'; 
				File::move($file_path,$destinationPath.$fileTmp); 
				if(file_exists($destinationPath.$fileTmp)){
					$url_file = '//file.'.config('app.url').'/'.$destinationPath.$fileTmp;
					$data_file_insert = array(
						'member_id' => Auth::user()->id,
						'media_name' => $filename, 
						'media_content'=>$content, 
						'media_path' => $destinationPath,
						'media_url' 	=> $url_file, 
						'media_url_thumb' 	=> $url_file_thumb,
						'media_url_small' 	=> $url_file_small,
						'media_url_xs' 	=> $url_file_xs,
						'media_size' => filesize($file_path), 
						'media_type' =>mime_content_type($file_path), 
						'created_at'=>Carbon::now()->format('Y-m-d H:i:s'), 
						'media_storage'=>'files'
					);
					$id_media=Media::insertGetId($data_file_insert); 
					if($id_media){
						Media_join_channel::insertGetId(array(
							'media_id'=>$id_media, 
							'channel_id'=>$channelId
						)); 
						$getPost=Posts::find($postId); 
						if(!empty($getPost->id)){
							Media_join_post::insertGetId(array(
								'post_id'=>$postId, 
								'media_id'=>$id_media
							)); 
						}
						File::delete($destinationPathTmp.$fileTmp); 
						$getMedia=Media::find($id_media); 
						return $getMedia; 
					}
				}
			}
		}
	}
	public function keyRandom(){
		$pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
		$keyRandom=substr(str_shuffle(str_repeat($pool, 5)), 0, 5).strtotime(Carbon::now()->format('Y-m-d H:i:s')); 
		return $keyRandom; 
	}
	public function makeDir(){
		$dateFolder=[
			'day'=>date('d', strtotime(Carbon::now()->format('Y-m-d H:i:s'))), 
			'month'=>date('m', strtotime(Carbon::now()->format('Y-m-d H:i:s'))), 
			'year'=>date('Y', strtotime(Carbon::now()->format('Y-m-d H:i:s')))
		]; 
		$path = public_path(). '/media/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day']; 
		$pathVideos = public_path(). '/media/videos/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day']; 
		$pathFiles = public_path(). '/media/files/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day']; 
		if(!File::exists($path)) {
			File::makeDirectory($path, $mode = 0777, true, true); 
		}
		if(!File::exists($path.'/thumb')) {
			File::makeDirectory($path.'/thumb', $mode = 0777, true, true);
		}
		if(!File::exists($path.'/small')) {
			File::makeDirectory($path.'/small', $mode = 0777, true, true);
		}
		if(!File::exists($path.'/xs')) {
			File::makeDirectory($path.'/xs', $mode = 0777, true, true);
		}
		if(!File::exists($pathVideos)) {
			File::makeDirectory($pathVideos, $mode = 0777, true, true); 
		}
		if(!File::exists($pathVideos.'/thumb')) {
			File::makeDirectory($pathVideos.'/thumb', $mode = 0777, true, true); 
		}
		if(!File::exists($pathVideos.'/small')) {
			File::makeDirectory($pathVideos.'/small', $mode = 0777, true, true); 
		}
		if(!File::exists($pathVideos.'/xs')) {
			File::makeDirectory($pathVideos.'/xs', $mode = 0777, true, true); 
		}
		if(!File::exists($pathFiles)) {
			File::makeDirectory($pathFiles, $mode = 0777, true, true); 
		}
		return $dateFolder; 
	}
}