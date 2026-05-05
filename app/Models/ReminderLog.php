<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReminderLog extends Model
{
    protected $fillable = [
        'form_release_id', 'participant_id', 'channel', 'sent_at', 'status', 'error_message',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function formRelease(): BelongsTo
    {
        return $this->belongsTo(FormRelease::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }
}
