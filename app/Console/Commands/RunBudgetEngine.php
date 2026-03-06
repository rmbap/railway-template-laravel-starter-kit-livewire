<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class RunBudgetEngine extends Command
{
    protected $signature = 'engine:run';
    protected $description = 'Run marketing budget decision engine';

    public function handle()
    {
        $this->info("Budget engine running...");
    }
}
