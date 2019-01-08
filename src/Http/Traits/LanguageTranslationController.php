<?php 
namespace Ry\Admin\Http\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;
use Ry\Admin\Models\LanguageTranslation;
use Ry\Admin\Models\Permission;
use App;

trait LanguageTranslationController
{
    public function get_traductions(Request $request) {
        $langs = DB::table("ry_admin_language_translations")->selectRaw("DISTINCT(lang)")->get();
        $locale = App::getLocale();
        $languages = [];
        $i = 1;
        $fields = [];
        $joins = [];
        $perPage = 10;
        $page = $request->get('page', 1);
        $offset = ($page * $perPage) - $perPage;
        foreach($langs as $lang) {
            if($locale != $lang->lang) {
                $languages[$lang->lang] = [];
                $fields[] = "t$i.translation_string AS " . $lang->lang;
                $joins[] = "LEFT JOIN ry_admin_language_translations t$i ON t$i.translation_id = t0.translation_id AND t$i.lang = '".$lang->lang."'";
                $i++;
            }
        }
        $wheres = ["t0.lang = 'fr'"];
        $bindings = ['offset' => $offset, 'limit' => $perPage];
        if($request->has("s")) {
            $orwheres = [];
            $i = 0;
            foreach($langs as $lang) {
                $orwheres[] = "t$i.translation_string LIKE :s$i";
                $bindings['s'.$i] = '%'.$request->get('s').'%';
                $i++;
            }
            $wheres[] = "(" . implode(" OR ", $orwheres) . ")";
        }
        $query = "SELECT t0.translation_id, t0.translation_string AS fr, ".implode(",", $fields)."
                FROM `ry_admin_language_translations` t0
                ".implode(" ", $joins)."
                WHERE ".implode(" AND ", $wheres)." ORDER BY t0.translation_string LIMIT :offset,:limit";
        $result = DB::select($query, $bindings);
        $rows = new Paginator($result, 10);
        $ar = $rows->toArray();
        $permission = Permission::authorize($this);
        $ar['page'] = [
            "title" => __("Traductions"),
            "href" => '/'.request()->path(),
            "icon" => "fa fa-globe-africa",
            "permission" => $permission,
            "children" => []
        ];
        return view("$this->theme::traductions", $ar);
    }
    
    public function post_traductions(Request $request) {
        $translation = LanguageTranslation::where("translation_id", "=", $request->get("translation_id"))
        ->where("lang", "=", $request->get("lang"))->first();
        if(!$translation) {
            $translation = new LanguageTranslation();
            $translation->translation_id = $request->get("translation_id");
            $translation->lang = $request->get("lang");
            $translation->translation_string = $request->get("translation_string");
            $translation->save();
        }
        else {
            LanguageTranslation::where("translation_id", "=", $request->get("translation_id"))
            ->where("lang", "=", $request->get("lang"))->update([
                "translation_string" => $request->get("translation_string")
            ]);
        }
    }
    
    public function delete_traductions(Request $request) {
        $this->authorize('ryadmin.language_translation.*');
        LanguageTranslation::where("translation_id", "=", $request->get("translation_id"))->delete();
    }
    
    public function post_languages(Request $request) {
        return view("$this->theme::dialogs.languages");
    }
}
?>