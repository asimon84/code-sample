<?php

namespace App\Console\Commands;

use App\Models\Customer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class FlushCustomersCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:flush-customers-cache';

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
     *
     * @return int
     */
    public function handle()
    {
        $customers = Customer::all();

        // Clear redis just for testing and demonstration purposes only
        // Also just wanted to demonstrate redis pipeline and collection filter
        Redis::pipeline(function ($pipe) use ($customers) {
            for ($i = 1; $i <= $customers->count(); $i++) {
                $customer = $customers->filter(function ($item) use ($i) {
                    return $item->id == $i;
                })->first();

                $pipe->del("customer:$i", $customer);
            }
        });
    }
}
