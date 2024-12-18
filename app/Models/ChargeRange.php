<?php

namespace App\Models;

use App\Filament\Resources\ChargeRangeResource;
use App\Notifications\ChargeRangeSubmitted;
use App\Notifications\ChargeRangeFinanceApproved;
use App\Notifications\ChargeRangeCEOApproved;
use App\Notifications\ChargeRangeRejected;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Log;

class ChargeRange extends Model
{
    protected $fillable = [
        'transaction_type_id',
        'min_amount',
        'max_amount',
        'charge_type',
        'flat_charge_amount',
        'percentage_charge_amount',
        'tax_type',
        'flat_tax_amount',
        'percentage_tax_amount',
        'approval_status',
        'rejection_reason',
        'created_by',
        'finance_approved_by',
        'ceo_approved_by',
        'finance_approved_at',
        'ceo_approved_at',
        'is_active'
    ];

    protected $casts = [
        'min_amount' => 'decimal:2',
        'max_amount' => 'decimal:2',
        'flat_charge_amount' => 'decimal:2',
        'percentage_charge_amount' => 'decimal:2',
        'flat_tax_amount' => 'decimal:2',
        'percentage_tax_amount' => 'decimal:2',
        'is_active' => 'boolean',
        'finance_approved_at' => 'datetime',
        'ceo_approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    // Relationships
    public function transactionType(): BelongsTo
    {
        return $this->belongsTo(TransactionType::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function financeApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }

    public function ceoApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ceo_approved_by');
    }

    // Status Check Methods
    public function isDraft(): bool
    {
        return $this->approval_status === 'draft';
    }

    public function isPendingFinance(): bool
    {
        return $this->approval_status === 'pending_finance';
    }

    public function isPendingCEO(): bool
    {
        return $this->approval_status === 'pending_ceo';
    }

    public function isApproved(): bool
    {
        return $this->approval_status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->approval_status === 'rejected';
    }

    // Helper Methods
    public function hasFlatCharge(): bool
    {
        return in_array($this->charge_type, ['flat', 'both']);
    }

    public function hasPercentageCharge(): bool
    {
        return in_array($this->charge_type, ['percentage', 'both']);
    }

    public function hasFlatTax(): bool
    {
        return in_array($this->tax_type, ['flat', 'both']);
    }

    public function hasPercentageTax(): bool
    {
        return in_array($this->tax_type, ['percentage', 'both']);
    }

    // Query Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePendingFinanceApproval($query)
    {
        return $query->where('approval_status', 'pending_finance');
    }

    public function scopePendingCEOApproval($query)
    {
        return $query->where('approval_status', 'pending_ceo');
    }

    public function scopeApproved($query)
    {
        return $query->where('approval_status', 'approved');
    }

    // Calculation Methods
    public function calculateCharges(float $amount): array
    {
        $serviceCharge = $this->calculateServiceCharge($amount);
        $governmentTax = $this->calculateTax($amount);

        $totalCharges = $serviceCharge['total'] + $governmentTax['total'];

        return [
            'amount' => $amount,
            'service_charge' => $serviceCharge,
            'government_tax' => $governmentTax,
            'total_charges' => $totalCharges,
            'total_amount' => $amount + $totalCharges
        ];
    }

    public function calculateServiceCharge(float $amount): array
    {
        $flatCharge = 0;
        $percentageCharge = 0;

        if ($this->hasFlatCharge()) {
            $flatCharge = $this->flat_charge_amount;
        }

        if ($this->hasPercentageCharge()) {
            $percentageCharge = ($amount * $this->percentage_charge_amount / 100);
        }

        return [
            'flat' => round($flatCharge, 2),
            'percentage' => round($percentageCharge, 2),
            'total' => round($flatCharge + $percentageCharge, 2)
        ];
    }

    public function calculateTax(float $amount): array
    {
        $flatTax = 0;
        $percentageTax = 0;

        if ($this->hasFlatTax()) {
            $flatTax = $this->flat_tax_amount;
        }

        if ($this->hasPercentageTax()) {
            $percentageTax = ($amount * $this->percentage_tax_amount / 100);
        }

        return [
            'flat' => round($flatTax, 2),
            'percentage' => round($percentageTax, 2),
            'total' => round($flatTax + $percentageTax, 2)
        ];
    }

    // Notification Methods
    protected function notifyFinanceTeam()
    {
        try {
            $financeApprovers = User::role('finance_approver')->get();
            Log::info('Notifying finance team. Found approvers: ' . $financeApprovers->count());

            $url = ChargeRangeResource::getUrl('index');

            foreach ($financeApprovers as $approver) {
                Notification::make()
                    ->title('New Charge Range for Approval')
                    ->icon('heroicon-o-credit-card')
                    ->body("A new charge range for {$this->transactionType->name} requires your approval.\nAmount Range: TZS " .
                        number_format($this->min_amount, 2) . " - " . number_format($this->max_amount, 2))
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url($url)
                    ])
                    ->sendToDatabase($approver);

                Log::info("Sending notification to finance approver: {$approver->email}");
                $approver->notify(new ChargeRangeSubmitted($this));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify finance team: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function notifyCEO()
    {
        try {
            $ceos = User::role('ceo')->get();
            Log::info('Notifying CEO. Found CEOs: ' . $ceos->count());

            $url = ChargeRangeResource::getUrl('index');

            foreach ($ceos as $ceo) {
                Notification::make()
                    ->title('Charge Range Awaiting Final Approval')
                    ->icon('heroicon-o-shield-check')
                    ->body("A charge range for {$this->transactionType->name} has been approved by finance.\nAmount Range: TZS " .
                        number_format($this->min_amount, 2) . " - " . number_format($this->max_amount, 2))
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url($url)
                    ])
                    ->sendToDatabase($ceo);

                Log::info("Sending notification to CEO: {$ceo->email}");
                $ceo->notify(new ChargeRangeFinanceApproved($this));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify CEO: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function notifyTeamOfApproval(): void
    {
        try {
            $creator = $this->creator;
            if ($creator) {
                $url = ChargeRangeResource::getUrl('index');

                Notification::make()
                    ->title('Charge Range Approved')
                    ->success()
                    ->icon('heroicon-o-check-circle')
                    ->body("Your charge range for {$this->transactionType->name} has been fully approved.\nAmount Range: TZS " .
                        number_format($this->min_amount, 2) . " - " . number_format($this->max_amount, 2))
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url($url)
                    ])
                    ->sendToDatabase($creator);

                Log::info("Sending approval notification to creator: {$creator->email}");
                $creator->notify(new ChargeRangeCEOApproved($this));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify team of approval: ' . $e->getMessage());
            throw $e;
        }
    }


    protected function notifyTeamOfRejection(string $reason)
    {
        try {
            $creator = $this->creator;
            if ($creator) {
                $url = ChargeRangeResource::getUrl('index');

                Notification::make()
                    ->title('Charge Range Rejected')
                    ->danger()
                    ->icon('heroicon-o-x-circle')
                    ->body("Your charge range for {$this->transactionType->name} was rejected.\nAmount Range: TZS " .
                        number_format($this->min_amount, 2) . " - " . number_format($this->max_amount, 2) .
                        "\nReason: {$reason}")
                    ->actions([
                        Action::make('view')
                            ->button()
                            ->url($url)
                    ])
                    ->sendToDatabase($creator);

                Log::info("Sending rejection notification to creator: {$creator->email}");
                $creator->notify(new ChargeRangeRejected($this, $reason));
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify team of rejection: ' . $e->getMessage());
            throw $e;
        }
    }

    // Approval Methods
    public function submitForFinanceApproval(int $userId): void
    {
        try {
            Log::info("Submitting charge range {$this->id} for finance approval");
            $this->update([
                'approval_status' => 'pending_finance',
                'created_by' => $userId
            ]);

            $this->notifyFinanceTeam();
            Log::info("Successfully submitted charge range {$this->id} for finance approval");
        } catch (\Exception $e) {
            Log::error("Error submitting charge range {$this->id} for finance approval: " . $e->getMessage());
            throw $e;
        }
    }

    public function approveByFinance(int $userId): void
    {
        try {
            Log::info("Finance approving charge range {$this->id}");
            $this->update([
                'approval_status' => 'pending_ceo',
                'finance_approved_by' => $userId,
                'finance_approved_at' => now()
            ]);

            $this->notifyCEO();
            Log::info("Successfully approved charge range {$this->id} by finance");
        } catch (\Exception $e) {
            Log::error("Error approving charge range {$this->id} by finance: " . $e->getMessage());
            throw $e;
        }
    }

    public function approveByCEO(int $userId): void
    {
        try {
            Log::info("CEO approving charge range {$this->id}");
            $this->update([
                'approval_status' => 'approved',
                'ceo_approved_by' => $userId,
                'ceo_approved_at' => now(),
                'is_active' => true
            ]);

            $this->notifyTeamOfApproval();
            Log::info("Successfully approved charge range {$this->id} by CEO");
        } catch (\Exception $e) {
            Log::error("Error approving charge range {$this->id} by CEO: " . $e->getMessage());
            throw $e;
        }
    }

    public function reject(string $reason, int $userId): void
    {
        try {
            Log::info("Rejecting charge range {$this->id}");
            $this->update([
                'approval_status' => 'rejected',
                'rejection_reason' => $reason,
                'is_active' => false
            ]);

            $this->notifyTeamOfRejection($reason);
            Log::info("Successfully rejected charge range {$this->id}");
        } catch (\Exception $e) {
            Log::error("Error rejecting charge range {$this->id}: " . $e->getMessage());
            throw $e;
        }
    }
}
