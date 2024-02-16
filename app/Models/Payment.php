<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'package_id',
        'amount',
        'method',
        'note',
        'ref_id',
        'isapproved',
    ];

    // packages
    public function package(): BelongsTo
    {
        return $this->belongsTo(AccountPackage::class, 'package_id');
    }

    // packages
    public function user(): BelongsTo
    {
        return $this->belongsTo(user::class, 'user_id');
    }
}
