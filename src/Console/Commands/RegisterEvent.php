<?php

namespace Ry\Admin\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class RegisterEvent extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ryadmin:event {action} {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a named event to inject in codes and list through events.log';

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
        $eventName = $this->argument('name');
        if(!Storage::disk("local")->exists("events.log")) {
            $events = [];
        }
        else {
            $events = json_decode(Storage::disk("local")->get("events.log"), true);
        }
        $events[$eventName] = [
            "latest_execution" => Carbon::now()
        ];
        Storage::disk("local")->put("events.log", json_encode($events));
    }
}
