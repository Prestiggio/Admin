<?php 
namespace Ry\Admin\Models\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Ry\Admin\Models\Archive;

trait ArchivableTrait
{
    private $archiver;
    
    public static $ARCHIVER_CLASS;
    
    public function archive() {
        if(static::$ARCHIVER_CLASS) {
            $archive = Archive::whereArchivableType(get_class($this))->whereArchivableId($this->id)->first();
            if(!$archive) {
                $archive = new Archive();
                $archive->archivable_type = get_class($this);
                $archive->archivable_id = $this->id;
                $archive->nsetup = [
                    'pending' => true
                ];
            }
            $archiver = $this->getArchiver();
            if($archiver->isClosed() && isset($archive->nsetup['closed'])) {
                //do nothing, archive has no need to be updated so far
            }
            else {
                $setup = $archive->nsetup;
                $setup['pending'] = true;
                $archive->nsetup = $setup;
                $archive->save();
                
                $result = $archiver->toArray();
                $result['pending'] = false;
                if($archiver->isClosed())
                    $result['closed'] = Carbon::now()->format('Y-m-d H:i:s');
                $archive->nsetup = $result;
                $archive->save();
            }
        }
    }
    
    public function getArchiver() {
        if(static::$ARCHIVER_CLASS) {
            if(!$this->archiver) {
                $this->archiver = new static::$ARCHIVER_CLASS($this);
            }
            return $this->archiver;
        }
        return $this;
    }
    
    public function toArray() {
        return $this->getArchiver()->toArray();
    }
    
    public function unarchive() {
        Archive::whereArchivableType(get_class($this))->whereArchivableId($this->id)->delete();
    }
}
?>