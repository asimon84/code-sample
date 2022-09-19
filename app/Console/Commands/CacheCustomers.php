<?php

namespace App\Console\Commands;

use App\Jobs\ProcessCustomers;
use Illuminate\Console\Command;

class CacheCustomers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:cache-customers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     */
    public function handle()
    {
        ProcessCustomers::dispatch()->onQueue('default');
    }
}
