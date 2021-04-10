<?php

namespace App\Jobs;

use App\Mail\CoopRefunded;
use App\Models\Purchase;
use App\Refund\Refund;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class RefundPurchaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $purchase;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->purchase->update(['coop_canceled' => true]);

        tap(new Refund($this->purchase))->make();

        if (!$this->purchase->coop->purchases()->where('coop_canceled', false)->get()->count()){
            $this->purchase->coop->update(['status' => 'canceled']);

            Mail::send(new CoopRefunded($this->purchase->coop));
        }
    }
}
