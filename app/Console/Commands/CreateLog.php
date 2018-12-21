<?php

namespace Nh\Console\Commands;

use Illuminate\Console\Command;

class CreateLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:create-log';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ghi 1 dòng log vào file Log trong laravel';

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
        \Log::info('Ghi 1 dòng log');
    }
}
