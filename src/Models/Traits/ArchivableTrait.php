<?php 
namespace Ry\Admin\Models\Traits;

use Illuminate\Http\Request;
use Ry\Admin\Models\Archive;

trait ArchivableTrait
{
    private $archived = false;
    
    public function archive($insert, $callback, $else_callback=null, $pending_callback=null) {
        if(!$insert && !$pending_callback) {
            $is_archived = false;
            if($else_callback) {
                $is_archived = call_user_func_array($else_callback, [$this, true]);
            }
            if(!$is_archived) {
                call_user_func_array($callback, [$this]);
                $this->archived = call_user_func_array($else_callback, [$this]);
            }
            else {
                $this->archived = call_user_func_array($else_callback, [$this]);
            }
        }
        else {
            $request = Request::capture();
            $archive = Archive::whereArchivableType(get_class($this))->whereArchivableId($this->id)->first();
            if($archive) {
                $this->archived = $archive->nsetup;
                $this->archived['last_updated_at'] = $archive->update_at;
            }
            elseif($request->userAgent() && $pending_callback) {
                $this->archived = call_user_func_array($pending_callback, [$this]);
                $this->archived['pending'] = true;
            }
            else {
                call_user_func_array($callback, [$this]);
                $result = $this->toArray();
                $archive = new Archive();
                $archive->archivable_type = get_class($this);
                $archive->archivable_id = $this->id;
                $archive->nsetup = $result;
                $archive->save();
                $this->archived = $result;
                $this->archived['last_updated_at'] = $archive->update_at;
            }
        }
    }
    
    protected function isArchived() {
        if(!$this->archived && Archive::whereArchivableType(get_class($this))->whereArchivableId($this->id)->exists()) {
            $archive = Archive::whereArchivableType(get_class($this))->whereArchivableId($this->id)->first();
            $this->archived = $archive->nsetup;
            $this->archived['archived'] = true;
        }
        return $this->archived;
    }
    
    public function toArray() {
        $ar = parent::toArray();
        if($this->isArchived()) {
            return array_replace_recursive($ar, $this->archived);
        }
        return $ar;
    }
    
    public function unarchive() {
        Archive::whereArchivableType(get_class($this))->whereArchivableId($this->id)->delete();
    }
}
?>