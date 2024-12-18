<?php

namespace App\Notifications;

use App\Models\ChargeRange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChargeRangeSubmitted extends Notification
{
    use Queueable;

    protected $chargeRange;

    public function __construct(ChargeRange $chargeRange)
    {
        $this->chargeRange = $chargeRange;
    }

    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('New Charge Range Awaiting Finance Approval')
            ->line('A new charge range has been submitted for approval.')
            ->line("Transaction Type: {$this->chargeRange->transactionType->name}")
            ->line("Amount Range: TZS " . number_format($this->chargeRange->min_amount, 2) .
                " - TZS " . number_format($this->chargeRange->max_amount, 2))
            ->action('View Charge Range', url("/admin/charge-ranges/{$this->chargeRange->id}"))
            ->line('Please review and take appropriate action.');
    }

    public function toArray($notifiable)
    {
        return [
            'charge_range_id' => $this->chargeRange->id,
            'message' => 'New charge range awaiting finance approval',
            'transaction_type' => $this->chargeRange->transactionType->name,
        ];
    }
}

