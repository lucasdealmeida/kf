<?php

namespace App\Console\Commands;

use App\Jobs\CoopExpiredJob;
use App\Models\Coop;
use Illuminate\Console\Command;

class CoopExpired extends Command
{
    protected $signature = 'coop-expired';

    protected $description = 'Create a job for each Expired Coop';

    public function handle()
    {
        Coop::expired()->ofStatus('active')->get()->each(function ($coop) {
            CoopExpiredJob::dispatch($coop);
        });
    }
}
