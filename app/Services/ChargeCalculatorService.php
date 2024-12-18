<?php

// app/Services/ChargeCalculatorService.php
namespace App\Services;

use App\Models\TransactionType;
use App\Models\ChargeRange;

class ChargeCalculatorService
{
    public function calculateCharges(string $transactionType, float $amount)
    {
        $type = TransactionType::where('code', $transactionType)
            ->where('is_active', true)
            ->firstOrFail();

        $range = ChargeRange::where('transaction_type_id', $type->id)
            ->where('min_amount', '<=', $amount)
            ->where('max_amount', '>=', $amount)
            ->where('is_active', true)
            ->firstOrFail();

        // Calculate service charges based on type
        $serviceCharge = $this->calculateServiceCharge($range, $amount);

        // Calculate government tax based on type
        $governmentTax = $this->calculateGovernmentTax($range, $amount);

        $totalCharges = $serviceCharge['total'] + $governmentTax['total'];

        return [
            'amount' => $amount,
            'service_charge' => [
                'flat' => $serviceCharge['flat'],
                'percentage' => $serviceCharge['percentage'],
                'total' => $serviceCharge['total']
            ],
            'government_tax' => [
                'flat' => $governmentTax['flat'],
                'percentage' => $governmentTax['percentage'],
                'total' => $governmentTax['total']
            ],
            'total_charges' => $totalCharges,
            'total_amount' => $amount + $totalCharges,
            'transaction_type' => $type->name,
            'range' => [
                'min' => $range->min_amount,
                'max' => $range->max_amount
            ]
        ];
    }

    private function calculateServiceCharge(ChargeRange $range, float $amount): array
    {
        $flatCharge = 0;
        $percentageCharge = 0;

        switch ($range->charge_type) {
            case 'flat':
                $flatCharge = $range->flat_charge_amount;
                break;
            case 'percentage':
                $percentageCharge = ($amount * $range->percentage_charge_amount / 100);
                break;
            case 'both':
                $flatCharge = $range->flat_charge_amount;
                $percentageCharge = ($amount * $range->percentage_charge_amount / 100);
                break;
        }

        return [
            'flat' => round($flatCharge, 2),
            'percentage' => round($percentageCharge, 2),
            'total' => round($flatCharge + $percentageCharge, 2)
        ];
    }

    private function calculateGovernmentTax(ChargeRange $range, float $amount): array
    {
        $flatTax = 0;
        $percentageTax = 0;

        switch ($range->tax_type) {
            case 'flat':
                $flatTax = $range->flat_tax_amount;
                break;
            case 'percentage':
                $percentageTax = ($amount * $range->percentage_tax_amount / 100);
                break;
            case 'both':
                $flatTax = $range->flat_tax_amount;
                $percentageTax = ($amount * $range->percentage_tax_amount / 100);
                break;
        }

        return [
            'flat' => round($flatTax, 2),
            'percentage' => round($percentageTax, 2),
            'total' => round($flatTax + $percentageTax, 2)
        ];
    }
}
