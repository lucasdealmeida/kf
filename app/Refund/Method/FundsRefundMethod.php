<?php

namespace App\Refund\Method;

class FundsRefundMethod extends RefundMethod
{
    public function handle()
    {
        $this->createTransaction();
    }
}
