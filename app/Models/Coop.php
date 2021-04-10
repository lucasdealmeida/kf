<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coop extends Model
{
    use HasFactory;

    protected $fillable = ['status'];

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function owner()
    {
        return $this->belongsTo(Brand::class);
    }

    public function hasBeenFullyFunded()
    {
        return $this->purchases->sum->amount >= $this->goal;
    }

    public function scopeExpired($query)
    {
        return $query->where('expiration_date', '<=', now());
    }

    public function scopeOfStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
