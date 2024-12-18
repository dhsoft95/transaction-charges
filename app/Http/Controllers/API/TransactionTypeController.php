<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionTypeController extends Controller
{
    public function index()
    {
        $types = TransactionType::with(['chargeRanges' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->get()
            ->map(function ($type) {
                $type->chargeRanges->transform(function ($range) {
                    return [
                        'id' => $range->id,
                        'min_amount' => $range->min_amount,
                        'max_amount' => $range->max_amount,
                        'charge_details' => $this->formatChargeDetails($range),
                        'tax_details' => $this->formatTaxDetails($range),
                        'is_active' => $range->is_active,
                    ];
                });
                return $type;
            });

        return response()->json([
            'status' => 'success',
            'data' => $types
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'code' => 'required|string|unique:transaction_types,code',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $type = TransactionType::create($request->all());

        return response()->json([
            'status' => 'success',
            'data' => $type
        ], 201);
    }

    private function formatChargeDetails($range): array
    {
        $details = [
            'type' => $range->charge_type,
        ];

        switch ($range->charge_type) {
            case 'flat':
                $details['flat_amount'] = $range->flat_charge_amount;
                break;
            case 'percentage':
                $details['percentage'] = $range->percentage_charge_amount;
                break;
            case 'both':
                $details['flat_amount'] = $range->flat_charge_amount;
                $details['percentage'] = $range->percentage_charge_amount;
                break;
        }

        return $details;
    }

    private function formatTaxDetails($range): array
    {
        $details = [
            'type' => $range->tax_type,
        ];

        switch ($range->tax_type) {
            case 'flat':
                $details['flat_amount'] = $range->flat_tax_amount;
                break;
            case 'percentage':
                $details['percentage'] = $range->percentage_tax_amount;
                break;
            case 'both':
                $details['flat_amount'] = $range->flat_tax_amount;
                $details['percentage'] = $range->percentage_tax_amount;
                break;
        }

        return $details;
    }
}
