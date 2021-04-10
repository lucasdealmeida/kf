<?php

namespace App\Jobs;

use App\Mail\CoopCanceled;
use App\Mail\CoopCompletedAndGoalAchieved;
use App\Models\Coop;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class CoopExpiredJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $coop;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Coop $coop)
    {
        $this->coop = $coop;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->coop->status != 'active'){
            return;
        }

        if ($this->coop->hasBeenFullyFunded()) {
            Mail::send(new CoopCompletedAndGoalAchieved($this->coop));
            $this->coop->update(['status' => 'completed']);

            return;
        }

        if ($this->coop->purchases->count()) {
            $this->coop->update(['status' => 'refunding']);

            $this->coop->purchases->each(function($purchase){
                RefundPurchaseJob::dispatch($purchase);
            });

            return;
        }

        Mail::send(new CoopCanceled($this->coop));
        $this->coop->update(['status' => 'canceled']);
    }
}
