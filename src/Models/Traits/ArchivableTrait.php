<?php 
namespace Ry\Admin\Models\Traits;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Ry\Admin\Models\Archive;

trait ArchivableTrait
{
    private $archiver;
    
    public function archive() {
        if(app("centrale")->getArchiver(get_class($this))) {
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
                
                $result = $archiver->unclosedToArray();
                $result['pending'] = false;
                if($archiver->isClosed())
                    $result['closed'] = Carbon::now()->format('Y-m-d H:i:s');
                $archive->nsetup = $result;
                $archive->save();
            }
        }
    }
    
    public function getArchiver() {
        if(app("centrale")->getArchiver(get_class($this))) {
            if(!$this->archiver) {
                $c = app("centrale")->getArchiver(get_class($this));
                $this->archiver = new $c($this);
            }
            return $this->archiver;
        }
        return $this;
    }
    
    public function toArray() {
        if(app("centrale")->getArchiver(get_class($this))) {
            return $this->getArchiver()->toArray();
        }
        return parent::toArray();
    }
    
    public function unarchive() {
        Archive::whereArchivableType(get_class($this))->whereArchivableId($this->id)->delete();
    }
}
?>