<?php

namespace Ry\Admin\Console\Commands;

use Illuminate\Console\Command;
use Ry\Admin\Models\Layout\Layout;

class RegisterLayoutSection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ryadmin:section {layout=admin} {section=menu}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Register a section to be managed by user and in admin panel from integrator';

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
        Layout::where('name', 'LIKE', $this->argument('layout'))->first()->sections()->create([
            "name" => $this->argument('section'),
            "active" => true
        ]);
        $this->info("Section : ".$this->argument('section') . " a été rajouté dans l'espace " . $this->argument('layout'));
    }
}
