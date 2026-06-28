<?php

namespace App\Notifications;

use App\Models\UploadBatch;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;

class BatchReviewReadyNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly UploadBatch $batch,
    ) {}

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bulk Upload Ready for Review')
            ->line("Your batch {$this->batch->id} is ready for review.")
            ->line("Total pages: {$this->batch->total_pages}")
            ->action('Review Now', url('/admin/bulk-upload-review'));
    }

    public function toDatabase($notifiable): array
    {
        return [
            'batch_uuid' => $this->batch->id,
            'message' => 'Batch ready for review',
            'total_pages' => $this->batch->total_pages,
        ];
    }
}
