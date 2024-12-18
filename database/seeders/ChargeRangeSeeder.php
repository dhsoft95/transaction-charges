<?php

namespace Database\Seeders;

use App\Models\ChargeRange;
use App\Models\TransactionType;
use Illuminate\Database\Seeder;

class ChargeRangeSeeder extends Seeder
{
    public function run()
    {
        // Simba Money to Simba Money (P2P)
        $this->createP2PCharges();

        // MNO to Simba Money Charges (Tigo, Mpesa, Airtel)
        $this->createMNOToSimbaCharges();

        // Simba Money to MNO Wallets
        $this->createSimbaToMNOCharges();

        // Simba Money to Bank Transfers
        $this->createSimbaToBankCharges();

        // Simba Money to Merchant Payments
        $this->createSimbaToMerchantCharges();
    }

    private function createP2PCharges()
    {
        $type = TransactionType::where('code', 'SIMBA_TO_SIMBA')->first();

        if ($type) {
            $ranges = [
                [
                    'min_amount' => 500,
                    'max_amount' => 10000,
                    'charge_type' => 'flat',
                    'flat_charge_amount' => 100,
                    'tax_type' => 'percentage',
                    'percentage_tax_amount' => 2,
                ],
                [
                    'min_amount' => 10001,
                    'max_amount' => 50000,
                    'charge_type' => 'flat',
                    'flat_charge_amount' => 300,
                    'tax_type' => 'percentage',
                    'percentage_tax_amount' => 2,
                ],
                [
                    'min_amount' => 50001,
                    'max_amount' => 1000000,
                    'charge_type' => 'both',
                    'flat_charge_amount' => 500,
                    'percentage_charge_amount' => 0.1,
                    'tax_type' => 'percentage',
                    'percentage_tax_amount' => 2,
                ],
            ];

            foreach ($ranges as $range) {
                $this->createChargeRange($type->id, $range);
            }
        }
    }

    private function createMNOToSimbaCharges()
    {
        $mnoTypes = ['TIGO_TO_SIMBA', 'MPESA_TO_SIMBA', 'AIRTEL_TO_SIMBA'];

        foreach ($mnoTypes as $typeCode) {
            $type = TransactionType::where('code', $typeCode)->first();

            if ($type) {
                $ranges = [
                    [
                        'min_amount' => 1000,
                        'max_amount' => 20000,
                        'charge_type' => 'flat',
                        'flat_charge_amount' => 200,
                        'tax_type' => 'percentage',
                        'percentage_tax_amount' => 2,
                    ],
                    [
                        'min_amount' => 20001,
                        'max_amount' => 100000,
                        'charge_type' => 'flat',
                        'flat_charge_amount' => 500,
                        'tax_type' => 'percentage',
                        'percentage_tax_amount' => 2,
                    ],
                    [
                        'min_amount' => 100001,
                        'max_amount' => 3000000,
                        'charge_type' => 'percentage',
                        'percentage_charge_amount' => 1,
                        'tax_type' => 'percentage',
                        'percentage_tax_amount' => 2,
                    ],
                ];

                foreach ($ranges as $range) {
                    $this->createChargeRange($type->id, $range);
                }
            }
        }
    }

    private function createSimbaToMNOCharges()
    {
        $type = TransactionType::where('code', 'SIMBA_TO_MNO')->first();

        if ($type) {
            $ranges = [
                [
                    'min_amount' => 1000,
                    'max_amount' => 50000,
                    'charge_type' => 'flat',
                    'flat_charge_amount' => 400,
                    'tax_type' => 'percentage',
                    'percentage_tax_amount' => 2,
                ],
                [
                    'min_amount' => 50001,
                    'max_amount' => 1000000,
                    'charge_type' => 'percentage',
                    'percentage_charge_amount' => 1.2,
                    'tax_type' => 'percentage',
                    'percentage_tax_amount' => 2,
                ],
            ];

            foreach ($ranges as $range) {
                $this->createChargeRange($type->id, $range);
            }
        }
    }

    private function createSimbaToBankCharges()
    {
        $bankTypes = [
            'SIMBA_TO_LOCAL_BANKS',
            'SIMBA_TO_ECO_BANK',
            'SIMBA_TO_NMB_BILLS',
            'SIMBA_TO_INT_BANKS'
        ];

        foreach ($bankTypes as $typeCode) {
            $type = TransactionType::where('code', $typeCode)->first();

            if ($type) {
                $ranges = [
                    [
                        'min_amount' => 10000,
                        'max_amount' => 100000,
                        'charge_type' => 'flat',
                        'flat_charge_amount' => 1000,
                        'tax_type' => 'percentage',
                        'percentage_tax_amount' => 2,
                    ],
                    [
                        'min_amount' => 100001,
                        'max_amount' => 1000000,
                        'charge_type' => 'both',
                        'flat_charge_amount' => 1500,
                        'percentage_charge_amount' => 0.5,
                        'tax_type' => 'percentage',
                        'percentage_tax_amount' => 2,
                    ],
                    [
                        'min_amount' => 1000001,
                        'max_amount' => 10000000,
                        'charge_type' => 'percentage',
                        'percentage_charge_amount' => 1,
                        'tax_type' => 'percentage',
                        'percentage_tax_amount' => 2,
                    ],
                ];

                foreach ($ranges as $range) {
                    $this->createChargeRange($type->id, $range);
                }
            }
        }
    }

    private function createSimbaToMerchantCharges()
    {
        $type = TransactionType::where('code', 'SIMBA_TO_SELCOM_MERCHANT')->first();

        if ($type) {
            $ranges = [
                [
                    'min_amount' => 1000,
                    'max_amount' => 50000,
                    'charge_type' => 'flat',
                    'flat_charge_amount' => 200,
                    'tax_type' => 'percentage',
                    'percentage_tax_amount' => 2,
                ],
                [
                    'min_amount' => 50001,
                    'max_amount' => 500000,
                    'charge_type' => 'percentage',
                    'percentage_charge_amount' => 0.8,
                    'tax_type' => 'percentage',
                    'percentage_tax_amount' => 2,
                ],
                [
                    'min_amount' => 500001,
                    'max_amount' => 5000000,
                    'charge_type' => 'percentage',
                    'percentage_charge_amount' => 0.5,
                    'tax_type' => 'percentage',
                    'percentage_tax_amount' => 2,
                ],
            ];

            foreach ($ranges as $range) {
                $this->createChargeRange($type->id, $range);
            }
        }
    }

    private function createChargeRange($typeId, $data)
    {
        ChargeRange::create(array_merge($data, [
            'transaction_type_id' => $typeId,
            'approval_status' => 'approved',
            'is_active' => true,
            // Initialize nullable fields
            'flat_charge_amount' => $data['charge_type'] === 'percentage' ? null : ($data['flat_charge_amount'] ?? null),
            'percentage_charge_amount' => $data['charge_type'] === 'flat' ? null : ($data['percentage_charge_amount'] ?? null),
            'flat_tax_amount' => $data['tax_type'] === 'percentage' ? null : ($data['flat_tax_amount'] ?? null),
            'percentage_tax_amount' => $data['tax_type'] === 'flat' ? null : ($data['percentage_tax_amount'] ?? null),
        ]));
    }
}
