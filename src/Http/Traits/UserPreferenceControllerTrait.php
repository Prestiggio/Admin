<?php
namespace Ry\Admin\Http\Traits;

use Illuminate\Http\Request;
use Ry\Admin\Models\UserPreference;

trait UserPreferenceControllerTrait
{
    public function post_preferences(Request $request) {
        $me = auth()->user();
        if($me) {
            $preferences = [];
            if($me->preference && $me->preference->ardata) {
                $preferences = $me->preference->ardata;
            }
            else {
                $me->preference = new UserPreference();
                $me->preference->user_id = $me->id;
            }
            $ar = $request->all();
            $me->preference->ardata = array_replace_recursive($preferences, $ar);
            $me->preference->save();
        }
    }
}
?>