<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'coop_id',
        'buyer_id',
        'type',
        'amount',
        'source',
        'memo',
        'is_pending',
        'is_canceled',
    ];

    public static function sources()
    {
        return [
            'Check',
            'CreditCard',
            'KickfurtherCredits',
            'KickfurtherFunds',
            'Wire',
        ];
    }

    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function buyer()
    {
        return $this->belongsTo(Buyer::class);
    }
}
