<?php

namespace App\Notifications;

use App\Models\UploadBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class BatchFailedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly UploadBatch $batch,
        public readonly string $reason,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bulk Upload Failed')
            ->line("Batch {$this->batch->id} failed.")
            ->line("Reason: {$this->reason}")
            ->action('View Batch', url('/admin'));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'batch_uuid' => $this->batch->id,
            'message' => 'Batch failed',
            'reason' => $this->reason,
        ];
    }
}
