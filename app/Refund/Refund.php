<?php

namespace App\Refund;

use App\Refund\Method\CreditRefundMethod;
use App\Stripe\CancelCharge;
use App\Models\Purchase;
use App\Refund\Method\CreditCardRefundMethod;
use App\Refund\Method\FundsRefundMethod;

class Refund
{
    public $purchase;

    public $purchaseTransaction;

    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;

        $this->purchaseTransaction = $purchase->purchaseTransaction()->first();
    }

    public function make()
    {
        if ($this->purchaseTransaction->is_pending) {
            $this->purchaseTransaction->update([
                'is_pending'  => false,
                'is_canceled' => true,
            ]);

            tap(new CancelCharge())->refund($this->purchaseTransaction->id);

            return;
        }

        $this->getMethod()->handle();
    }

    protected function getMethod()
    {
        if ($this->purchaseTransaction->source == 'KickfurtherFunds') {
            return new FundsRefundMethod($this->purchaseTransaction, 'KickfurtherFunds');
        }

        if ($this->purchaseTransaction->source == 'CreditCard' and $this->purchase->buyer->refund_pref == 'cc') {
            return new CreditCardRefundMethod($this->purchaseTransaction, 'CreditCard');
        }

        return new CreditRefundMethod($this->purchaseTransaction, 'KickfurtherCredits');
    }
}
