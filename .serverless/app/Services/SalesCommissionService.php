<?php

namespace App\Services;

use App\Models\Sale;
use App\Models\SaleCommission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SalesCommissionService
{
    // Define the commission percentages for each level
    protected $commissionLevels = [
        1 => 0.10,  // 10% for the affiliate who referred the vendor
        2 => 0.05,  // 5% for the affiliate who referred the level 1 affiliate
        3 => 0.02,  // 2% for the affiliate who referred the level 2 affiliate
    ];

    /**
     * Distribute commissions for a sale.
     *
     * @param Sale $sale
     * @return void
     */
    public function distribute(Sale $sale)
    {
        // Get the vendor who owns the product
        $vendor = $sale->product->vendor;

        // Get the affiliate who referred the vendor
        $referrer = $vendor->ref_id ? User::where('my_ref_id', '=', $vendor->ref_id)->first() : null;

        $level = 1;  // Start with direct vendor's referrer (Level 1 Affiliate)
        $amount = $sale->total_amount;

        DB::transaction(function () use ($referrer, $amount, $sale, $level) {
            while ($referrer && $level <= 3) {  // Max 3 levels for commission
                if (isset($this->commissionLevels[$level])) {
                    // Calculate the commission for this level
                    $commissionAmount = $amount * $this->commissionLevels[$level];

                    // Store the commission in the commissions table
                    SaleCommission::create([
                        'user_id' => $referrer->id,  // Affiliate receiving commission
                        'sale_id' => $sale->id,      // Sale the commission is tied to
                        'level' => $level,
                        'amount' => $commissionAmount,
                    ]);

                    // Move up the referral chain to the next level
                    $referrer = $referrer->ref_id ? User::find($referrer->ref_id) : null;
                    $level++;
                }
            }
        });
    }
}
