<?php

namespace App\Refund\Method;

use App\Stripe\RefundCharge;

class CreditCardRefundMethod extends RefundMethod
{
    public function handle()
    {
        tap(new RefundCharge())->refund($this->purchaseTransaction->id, $this->purchaseTransaction->amount);

        $this->createTransaction();
    }
}
