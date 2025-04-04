<?php

namespace App\Services;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Facades\DB;

class CompensationService
{
    protected $compensationRates = [
        'Silver' => [
            'Silver' => [
                'base' => ['percentage' => 30, 'pv' => 6, 'pmt' => 200],
                'generations' => [
                    1 => 30,
                    2 => 5,
                    3 => 2,
                    4 => 2,
                    5 => 1,
                ]
            ],
            'Gold' => [
                'base' => ['percentage' => 25, 'pv' => 15, 'pmt' => 500],
                'generations' => [
                    1 => 25,
                    2 => 5,
                    3 => 2,
                    4 => 2,
                    5 => 1,
                ]
            ],
            'Diamond' => [
                'base' => ['percentage' => 20, 'pv' => 40, 'pmt' => 1500],
                'generations' => [
                    1 => 20,
                    2 => 5,
                    3 => 2,
                    4 => 2,
                    5 => 1,
                ]
            ],
        ],
        'Gold' => [
            'Silver' => [
                'base' => ['percentage' => 30, 'pv' => 6, 'pmt' => 200],
                'generations' => [
                    1 => 30,
                    2 => 6,
                    3 => 3,
                    4 => 2,
                    5 => 1,
                    6 => 1,
                    7 => 1,
                    8 => 1,
                ]
            ],
            'Gold' => [
                'base' => ['percentage' => 30, 'pv' => 15, 'pmt' => 500],
                'generations' => [
                    1 => 30,
                    2 => 6,
                    3 => 3,
                    4 => 2,
                    5 => 1,
                    6 => 1,
                    7 => 1,
                    8 => 1,
                ]
            ],
            'Diamond' => [
                'base' => ['percentage' => 25, 'pv' => 40, 'pmt' => 1500],
                'generations' => [
                    1 => 25,
                    2 => 6,
                    3 => 3,
                    4 => 2,
                    5 => 1,
                    6 => 1,
                    7 => 1,
                    8 => 1,
                ]
            ],
        ],
        'Diamond' => [
            'Silver' => [
                'base' => ['percentage' => 30, 'pv' => 6, 'pmt' => 200],
                'generations' => [
                    1 => 30,
                    2 => 8,
                    3 => 4,
                    4 => 2,
                    5 => 1,
                    6 => 1,
                    7 => 1,
                    8 => 1,
                    9 => 1,
                    10 => 1,
                ]
            ],
            'Gold' => [
                'base' => ['percentage' => 30, 'pv' => 15, 'pmt' => 500],
                'generations' => [
                    1 => 30,
                    2 => 8,
                    3 => 4,
                    4 => 2,
                    5 => 1,
                    6 => 1,
                    7 => 1,
                    8 => 1,
                    9 => 1,
                    10 => 1,
                ]
            ],
            'Diamond' => [
                'base' => ['percentage' => 30, 'pv' => 40, 'pmt' => 1500],
                'generations' => [
                    1 => 30,
                    2 => 8,
                    3 => 4,
                    4 => 2,
                    5 => 1,
                    6 => 1,
                    7 => 1,
                    8 => 1,
                    9 => 1,
                    10 => 1,
                ]
            ],
        ],
    ];

    public function processReferralCompensation(User $newUser)
    {
        // Only process if the new user is an Affiliate
        if (!in_array($newUser->user_type, ['Affiliate', 'Vendor']) || !$newUser->ref_id) {
            return;
        }

        try {
            DB::beginTransaction();

            // Get direct referrer first
            $directReferrer = User::where('my_ref_id', $newUser->ref_id)->first();
            if (!$directReferrer) {
                DB::commit();
                return;
            }

            // Get all upline users up to 10 generations
            switch ($directReferrer->package->name) {
                case 'Silver':
                    $uplineUsers = $directReferrer->getUplineUsers(5);
                    break;
                case 'Gold':
                    $uplineUsers = $directReferrer->getUplineUsers(8);
                    break;
                case 'Diamond':
                    $uplineUsers = $directReferrer->getUplineUsers(5);
                    break;
                default:
                    $uplineUsers = $directReferrer->getUplineUsers(10);
            }

            // Add direct referrer at the beginning
            $uplineUsers->prepend($directReferrer);

            // Get new user's package
            $newUserPackage = $newUser->package;
            if (!$newUserPackage) {
                DB::commit();
                return;
            }

            // Process compensation for each generation
            foreach ($uplineUsers as $generation => $uplineUser) {
                // Generation is 0-based, so add 1
                $generationLevel = $generation + 1;

                $this->processGenerationCompensation(
                    $uplineUser,
                    $newUserPackage,
                    $generationLevel
                );
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    protected function processGenerationCompensation(User $uplineUser, $newUserPackage, int $generation)
    {
        // Get upline user's package
        $uplinePackage = $uplineUser->package;
        if (!$uplinePackage) {
            return;
        }

        // Get compensation rates based on packages
        $rates = $this->compensationRates[$uplinePackage->name][$newUserPackage->name] ?? null;
        if (!$rates) {
            return;
        }

        // Get base rates and generation-specific percentage
        $baseRates = $rates['base'];
        $generationPercentage = $rates['generations'][$generation] ?? 0;
        if ($generationPercentage === 0) {
            return;
        }

        // Calculate compensation amount
        $compensationAmount = $newUserPackage->price * $generationPercentage / 100;
        // $compensationAmount = ($newUserPackage->price * $baseRates['percentage'] / 100) * ($generationPercentage / 100);

        // Update or create wallet for upline user
        $wallet = Wallet::firstOrCreate(
            ['user_id' => $uplineUser->id],
            ['amount' => 0, 'pv' => 0, 'pmt' => 0]
        );

        // Update wallet with generation compensation
        $wallet->increment('amount', $compensationAmount);
        $wallet->increment('pv', $baseRates['pv']); // Full PV for each generation
        $wallet->increment('pmt', $baseRates['pmt']); // Full PMT for each generation
    }
}
