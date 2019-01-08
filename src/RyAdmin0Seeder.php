<?php
namespace Ry\Admin;

use Illuminate\Database\Seeder;
use Ry\Admin\Models\Layout\Layout;

class RyAdmin0Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $layout = new Layout();
        $layout->name = "admin";
        $layout->save();
        $layout->sections()->createMany([
            [
                'name' => 'menu_top_lg',
                'active' => true,
                'default_setup' => json_encode([])
            ],
            [
                'name' => 'menu_sidebar_left',
                'active' => true,
                'default_setup' => json_encode([])
            ]
        ]);
    }
}
