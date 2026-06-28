<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkUploadEvent extends Model
{
    protected $fillable = [
        'batch_uuid',
        'preview_id',
        'event_type',
        'actor_id',
        'old_state',
        'new_state',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'old_state' => 'array',
            'new_state' => 'array',
            'metadata' => 'array',
        ];
    }

    public function batch()
    {
        return $this->belongsTo(UploadBatch::class, 'batch_uuid', 'id');
    }

    public function preview()
    {
        return $this->belongsTo(BulkUploadPreview::class, 'preview_id');
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
