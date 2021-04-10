<?php

namespace App\Refund\Method;

use App\Models\Transaction;

abstract class RefundMethod
{
    protected $purchaseTransaction;

    protected $source;

    public function __construct(Transaction $purchaseTransaction, String $source)
    {
        $this->purchaseTransaction = $purchaseTransaction;

        $this->source = $source;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function createTransaction()
    {
        $refundTransaction = Transaction::create([
            'purchase_id' => $this->purchaseTransaction->purchase_id,
            'buyer_id'    => $this->purchaseTransaction->buyer_id,
            'coop_id'     => $this->purchaseTransaction->coop_id,
            'type'        => 'refund',
            'amount'      => $this->purchaseTransaction->amount,
            'source'      => $this->getSource(),
            'memo'        => 'memo',
            'is_canceled' => false,
            'is_pending'  => false,
        ]);

        $this->purchaseTransaction->update([
            'refund_transaction_id' => $refundTransaction->id,
        ]);
    }

    abstract function handle();
}
