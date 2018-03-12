<?php

namespace Nh\Console\Commands;

use Illuminate\Console\Command;
use Nh\Repositories\Customers\Customer;

class UpdateUuid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:uuid';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update lại uuid customer sau khi import';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Customer $customer)
    {
        parent::__construct();
        $this->customer = $customer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $customers = $this->customer->get();
        foreach ($customers as $customer) {
            $customer->uuid = \Hashids::encode($customer->id);
            $customer->save();
        }
    }
}
