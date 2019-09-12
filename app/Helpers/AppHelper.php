<?php
namespace App\Helpers;
use Carbon\Carbon; 
use File; 
class AppHelper
{
	function addNofollow($html, $skip = null,$linkJson=false) {
		if($linkJson==true){
			return preg_replace_callback(
				'/<a(.*?)href="(.*?)"([^>]*?)>/', function ($mach) use ($skip) {
					if(!($skip && strpos($mach[2], $skip) !== false) && strpos($mach[2], 'rel=') === false){
						return '<a class="siteLink" data-url='.htmlentities(json_encode($mach[2])).' href="javascript:void(0);">';
					}else{
						return $mach[0];
					}
				},
				$html
			);
		}else{
			return preg_replace_callback(
				"#(<a[^>]+?)>#is", function ($mach) use ($skip) {
					return (
						!($skip && strpos($mach[1], $skip) !== false) &&
						strpos($mach[1], 'rel=') === false
					) ? $mach[1] . ' rel="nofollow">' : $mach[0];
				},
				$html
			);
		}
	}
	function time_request($time,$lang='')
    {
		if($lang=='en'){
			$minute=' minute ago'; 
			$hour=' hour ago'; 
			$day=' day ago'; 
			$month=' month ago'; 
			$year=' year ago'; 
		}else{
			$minute=' phút trước'; 
			$hour=' giờ trước'; 
			$day=' ngày trước'; 
			$month=' tháng trước'; 
			$year=' năm trước';
		}
        $date_current = date('Y-m-d H:i:s');
        $s = strtotime($date_current) - strtotime($time);
        if ($s <= 60) { // if < 60 seconds
            return '1 phút trước';
        }else
        {
            $t = intval($s / 60);
            if ($t >= 60) {
                $t = intval($t / 60);
                if ($t >= 24) {
                    $t = intval($t / 24);
                    if ($t >= 30) {
                        $t = intval($t / 30);
                        if ($t >= 12) {
                            $t = intval($t / 12);
                            return $t . $year;
                        } else {
                            return $t . $month;
                        }
                    } else {
                        return $t.$day;
                    }
                } else {
                    return $t.$hour;
                }
            } else {
                return $t.$minute;
            }
        }
    }
	function checkWordCC($str){ 
		$blacklist=preg_split("/(\r\n|\n|\r)/",File::get('words_cungcap.txt')); 
		foreach($blacklist as $a) {
			if (stripos($str,$a) !== false) 
			{
				return false; 
			}
		}
		return true; 
	}
	function checkBlacklistWord($str){ 
		$blacklist=preg_split("/(\r\n|\n|\r)/",File::get('words_blacklist.txt')); 
		foreach($blacklist as $a) {
			if (stripos($str,$a) !== false) 
			{
				return false; 
			}
		}
		return true; 
	}
	public function makeDir($root='img'){
		$dateFolder=[
			'day'=>date('d', strtotime(Carbon::now()->format('Y-m-d H:i:s'))), 
			'month'=>date('m', strtotime(Carbon::now()->format('Y-m-d H:i:s'))), 
			'year'=>date('Y', strtotime(Carbon::now()->format('Y-m-d H:i:s')))
		]; 
		$path = $root.'/'.$dateFolder['year'].'/'.$dateFolder['month'].'/'.$dateFolder['day']; 

		return $path; 
	}
	public static function price($price)
	{
		if(is_numeric($price)){
			return number_format($price, 0);
		}else{
			return 0; 
		}
	}
	public static function instance()
    {
        return new AppHelper();
    }
}