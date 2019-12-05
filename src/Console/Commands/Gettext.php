<?php

namespace Ry\Admin\Console\Commands;

use Illuminate\Console\Command;
use Ry\Admin\Http\Controllers\AdminController;

class Gettext extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ryadmin:gettext';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute python gettext insertions';

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
        app(AdminController::class)->pojson();
    }
}
