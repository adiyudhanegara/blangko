<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    protected $fillable = [
        'release_set_id', 'form_release_id', 'participant_id',
        'channel', 'reminder_offset_days', 'sent_at', 'status', 'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function releaseSet(): BelongsTo
    {
        return $this->belongsTo(ReleaseSet::class);
    }

    /** Kept for backward compatibility. */
    public function formRelease(): BelongsTo
    {
        return $this->belongsTo(FormRelease::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
