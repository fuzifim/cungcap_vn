<?php 
namespace App\Http\Controllers;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Request;
use Auth; 
use Theme; 
use Route; 
use App\Model\Note; 
use Cache; 
use Redirect; 
class ConstructController extends Controller
{
	public $_parame; 
	public $_rulesDomain; 
	public $_domainName; 
	public $_pieces; 
	public function __construct(){
		$this->_parame=Route::current()->parameters(); 
		$this->_rulesDomain = Cache::store('file')->remember('rulesDomain',500, function()
		{
			$pdp_url = 'https://raw.githubusercontent.com/publicsuffix/list/master/public_suffix_list.dat';
			$rules = \Pdp\Rules::createFromPath($pdp_url); 
			return $rules; 
		}); 
		
		Theme::uses('main');
		view()->share(
			'channel',array(
			)
		);
	}
}