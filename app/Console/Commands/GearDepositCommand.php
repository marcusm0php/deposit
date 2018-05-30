<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GearDepositCommand extends GearCommandBase
{
    protected $signature = 'command:gear:deposit';

    protected $description = 'Gearman Working: Deposit around functions.';

    public function __construct()
    {
        $this->beforeRun();
        
        parent::__construct();
    }

    public function handle()
    {
        
        
        
        
    }
}
