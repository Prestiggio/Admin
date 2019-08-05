<?php

namespace Ry\Admin\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Ry\Admin\Models\Alert;

class RegisterEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ryadmin:alert {action} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a named alert to inject in codes and list through alerts table';

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
        $alertName = $this->argument('name');
        $alert = new Alert();
        $alert->code = $alertName;
        $alert->descriptif = $this->ask('Décrivez le sens de cet évènement');
        $alert->nsetup = [
            'models' => [],
            'last_execution' => null
        ];
        $alert->save();
    }
}
