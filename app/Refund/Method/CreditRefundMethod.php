<?php

namespace App\Refund\Method;

class CreditRefundMethod extends RefundMethod
{
    public function handle()
    {
        $this->createTransaction();
    }
}
