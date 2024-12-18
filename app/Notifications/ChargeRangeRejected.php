<?php

namespace App\Notifications;

use App\Models\ChargeRange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChargeRangeRejected extends Notification
{
    use Queueable;

    protected $chargeRange;
    protected $rejectionReason;

    public function __construct(ChargeRange $chargeRange, string $rejectionReason)
    {
        $this->chargeRange = $chargeRange;
        $this->rejectionReason = $rejectionReason;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Charge Range Rejected')
            ->line('A charge range has been rejected.')
            ->line("Transaction Type: {$this->chargeRange->transactionType->name}")
            ->line("Amount Range: TZS " . number_format($this->chargeRange->min_amount, 2) .
                " - TZS " . number_format($this->chargeRange->max_amount, 2))
            ->line("Rejection Reason: {$this->rejectionReason}")
            ->action('View Charge Range', url("/admin/charge-ranges/{$this->chargeRange->id}"))
            ->line('Please review the rejection reason and make necessary adjustments.');
    }

    public function toArray($notifiable)
    {
        return [
            'charge_range_id' => $this->chargeRange->id,
            'message' => 'Charge range has been rejected',
            'transaction_type' => $this->chargeRange->transactionType->name,
            'rejection_reason' => $this->rejectionReason,
        ];
    }
}
