<?php

namespace App\Notifications;

use App\Models\ChargeRange;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ChargeRangeFinanceApproved extends Notification
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
            ->subject('Charge Range Approved by Finance - CEO Approval Required')
            ->line('A charge range has been approved by Finance and requires CEO approval.')
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
            'message' => 'Charge range approved by finance, awaiting CEO approval',
            'transaction_type' => $this->chargeRange->transactionType->name,
        ];
    }
}
