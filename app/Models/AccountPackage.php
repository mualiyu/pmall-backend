<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Yabacon\Paystack;

class AccountPackage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'type',
    ];

    /**
     * Get all of the comments for the AccountPackage
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'package_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'package_id');
    }

    public function init_payment($user)
    {
        try {
            $paystack = new Paystack(env('PAYSTACK_SECRET_KEY'));
            $tranx = $paystack->transaction->initialize([
                'amount' => $this->price * 100, // Amount in kobo (or cents)
                'email' => $user->email ? $user->email : $user->phone,
                'callback_url' => env('FRONT_URL').'/package/payment/verification',
                // 'callback_url' => url('/api/v1/customer/paystack/verify-callback'),
                'metadata' => [
                    'package_id' => $this->id, // Custom metadata
                    'user_id' => $user->id, // Custom metadata
                ],
            ]);

            return [
                'status' => true,
                'authorization_url' => $tranx->data->authorization_url,
                'reference' => $tranx->data->reference,
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage(),
            ];
        }
    }
}
