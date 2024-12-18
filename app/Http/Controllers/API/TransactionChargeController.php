<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Services\ChargeCalculatorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TransactionChargeController extends Controller
{
    protected $calculator;

    public function __construct(ChargeCalculatorService $calculator)
    {
        $this->calculator = $calculator;
    }

    public function calculateCharge(Request $request): \Illuminate\Http\JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'transaction_type' => 'required|string|exists:transaction_types,code',
            'amount' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $charges = $this->calculator->calculateCharges(
                $request->transaction_type,
                $request->amount
            );

            return response()->json([
                'status' => 'success',
                'data' => $charges
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to calculate charges. ' . $e->getMessage()
            ], 400);
        }
    }
}
