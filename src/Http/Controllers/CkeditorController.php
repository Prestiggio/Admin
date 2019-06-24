<?php 
namespace Ry\Admin\Http\Controllers;

use Illuminate\Filesystem\Filesystem;

class CkeditorController
{
    public function index($path) {
        $filesystem = new Filesystem();
        if(preg_match('/\.css$/', $path)) {
            return response($filesystem->get(__DIR__.'/../../assets/ckeditor/plugins/'.$path), 200, [
                'Content-Type' => 'text/css'
            ]);
        }
        return response($filesystem->get(__DIR__.'/../../assets/ckeditor/plugins/'.$path), 200, [
            'Content-Type' => $filesystem->mimeType(__DIR__.'/../../assets/ckeditor/plugins/'.$path)
        ]);
    }
    
    public function plugin() {
        
    }
}
?>