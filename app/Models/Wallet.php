<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount',
        'pmt',
        'pv',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // withdrawals made by the customer
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }
}
