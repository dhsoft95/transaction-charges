<?php

namespace App\Notifications;

use App\Models\ChargeRange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChargeRangeCEOApproved extends Notification
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
            ->subject('Charge Range Approved and Active')
            ->line('A charge range has been fully approved and is now active.')
            ->line("Transaction Type: {$this->chargeRange->transactionType->name}")
            ->line("Amount Range: TZS " . number_format($this->chargeRange->min_amount, 2) .
                " - TZS " . number_format($this->chargeRange->max_amount, 2))
            ->action('View Charge Range', url("/admin/charge-ranges/{$this->chargeRange->id}"))
            ->line('The charge range is now active in the system.');
    }

    public function toArray($notifiable)
    {
        return [
            'charge_range_id' => $this->chargeRange->id,
            'message' => 'Charge range approved by CEO and now active',
            'transaction_type' => $this->chargeRange->transactionType->name,
        ];
    }
}
