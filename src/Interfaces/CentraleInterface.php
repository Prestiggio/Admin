<?php 
namespace Ry\Admin\Interfaces;

use Illuminate\Http\Request;

interface CentraleInterface
{
    public function create($scoped_type, $scoped_id);
    
    public function activateUser(Request $request);
}
?>