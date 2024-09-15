<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'email',
        'username',
        'phone',
        'user_type',
        'status',
        'photo',
        'my_ref_id',
        'description',

        'store_name',
        'store_id',
        'store_url',
        'acct_name',
        'acct_number',
        'acct_type',
        'bank',
        'state',
        'lga',
        'address',
        'ref_id',

        'password',
        'role_id',
        'package_id',
        'isActive',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get all of the comments for the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'ref_id', 'my_ref_id');
    }

    public function downline()
    {
        return $this->hasMany(User::class, 'ref_id', 'my_ref_id');
    }

    public function allDownline()
    {
        return $this->downline()->with('allDownline');
    }

    /**
     * A user has a referrer.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ref_id', 'my_ref_id');
    }

    // packages
    public function package(): BelongsTo
    {
        return $this->belongsTo(AccountPackage::class, 'package_id');
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class, 'user_id');
    }


    // products
    public function products(): HasMany
    {
        return $this->hasMany(product::class, 'store_id', 'store_id');
    }
}
