<?php

namespace App\Notifications;

use App\Models\UploadBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class PublishCompletedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly UploadBatch $batch,
        public readonly int $published,
        public readonly int $failed,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bulk Upload Published')
            ->line("Batch {$this->batch->id} publishing complete.")
            ->line("Published: {$this->published}")
            ->line("Failed: {$this->failed}");
    }

    public function toDatabase($notifiable): array
    {
        return [
            'batch_uuid' => $this->batch->id,
            'message' => 'Publish complete',
            'published' => $this->published,
            'failed' => $this->failed,
        ];
    }
}
