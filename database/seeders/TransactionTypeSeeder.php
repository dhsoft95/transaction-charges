<?php

namespace Database\Seeders;

use App\Models\TransactionType;
use Illuminate\Database\Seeder;

class TransactionTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            [
                'name' => 'Tigo Wallet to Simba Money',
                'code' => 'TIGO_TO_SIMBA',
                'description' => 'Transfer from Tigo Pesa wallet to Simba Money',
                'is_active' => true
            ],
            [
                'name' => 'Vodacom Mpesa Wallet to Simba Money',
                'code' => 'MPESA_TO_SIMBA',
                'description' => 'Transfer from M-Pesa wallet to Simba Money',
                'is_active' => true
            ],
            [
                'name' => 'Airtel Wallet to Simba Money',
                'code' => 'AIRTEL_TO_SIMBA',
                'description' => 'Transfer from Airtel Money wallet to Simba Money',
                'is_active' => true
            ],
            [
                'name' => 'Local Int Banks to Simba Money',
                'code' => 'LOCAL_INT_BANKS_TO_SIMBA',
                'description' => 'Transfer from Local International Banks to Simba Money',
                'is_active' => true
            ],
            [
                'name' => 'Simba Money to Simba Money (P2P)',
                'code' => 'SIMBA_TO_SIMBA',
                'description' => 'Person to Person transfer between Simba Money accounts',
                'is_active' => true
            ],
            [
                'name' => 'Simba Money to MNO Wallets',
                'code' => 'SIMBA_TO_MNO',
                'description' => 'Transfer from Simba Money to Mobile Network Operator wallets',
                'is_active' => true
            ],
            [
                'name' => 'Simba Money to Local Bank (Eco)',
                'code' => 'SIMBA_TO_ECO_BANK',
                'description' => 'Transfer from Simba Money to Local Eco Bank',
                'is_active' => true
            ],
            [
                'name' => 'Simba Money to Local Banks',
                'code' => 'SIMBA_TO_LOCAL_BANKS',
                'description' => 'Transfer from Simba Money to Local Banks',
                'is_active' => true
            ],
            [
                'name' => 'Simba Money to NMB (Bill Payments)',
                'code' => 'SIMBA_TO_NMB_BILLS',
                'description' => 'Bill Payments from Simba Money to NMB Bank',
                'is_active' => true
            ],
            [
                'name' => 'Simba Money to SELCOM (Merchants)',
                'code' => 'SIMBA_TO_SELCOM_MERCHANT',
                'description' => 'Merchant payments from Simba Money to SELCOM',
                'is_active' => true
            ],
            [
                'name' => 'Simba Money to Int Banks',
                'code' => 'SIMBA_TO_INT_BANKS',
                'description' => 'Transfer from Simba Money to International Banks',
                'is_active' => true
            ]
        ];

        foreach ($types as $type) {
            TransactionType::create($type);
        }
    }
}
