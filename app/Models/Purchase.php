<?php

namespace App\Models;

use App\Refund\Method\CreditCardRefundMethod;
use App\Refund\Method\FundsRefundMethod;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'coop_canceled'
    ];

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }

    public function coop()
    {
        return $this->belongsTo(Coop::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public function purchaseTransaction()
    {
        return $this->hasOne(Transaction::class)->ofType('purchase');
    }

    public function refundTransaction()
    {
        return $this->hasOne(Transaction::class)->ofType('refund');
    }

    public function getRefundMethod()
    {
        $purchaseTransaction = $this->purchaseTransaction()->first();

        if (in_array($purchaseTransaction->source, ['KickfurtherCredits', 'KickfurtherFunds'])) {
            return new FundsRefundMethod($purchaseTransaction->source);
        }

        if ($purchaseTransaction->source == 'CreditCard' and $this->buyer->refund_pref == 'cc'){
            return new CreditCardRefundMethod($purchaseTransaction->source);
        }

        return new FundsRefundMethod('KickfurtherCredits');
    }
}
