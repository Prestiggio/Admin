<?php

namespace Ry\Admin\Console\Commands;

use Illuminate\Console\Command;
use Ry\Admin\Models\Permission;
use Illuminate\Support\Facades\Cache;

class Ability extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ryadmin:allow {role_name} {ability_name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Allow role by name to do an action';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Cache::forget("ryadmin.permissions");
    }
}
