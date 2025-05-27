<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Withdrawal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'wallet_id',
        'amount',
        'status', //pending, approved, rejected, completed
        'method',
        'reference',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public static function boot()
    {
        parent::boot();

        static::creating(function ($withdrawal) {
            $withdrawal->reference = self::generateUniqueReference();
        });
    }

    public static function generateUniqueReference()
    {
        do {
            $reference = 'PMALL-' . strtoupper(Str::random(10));
        } while (self::where('reference', $reference)->exists());

        return $reference;
    }
}
