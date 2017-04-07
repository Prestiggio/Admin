<?php
namespace Ry\Admin\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\View;
class AdminController extends Controller
{
	public function __construct() {
		View::share("js", json_encode(["conf" => null]));
	}
	
	public function getIndex() {
		
	}
}