<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    protected $fillable = [
        'form_release_id', 'participant_id', 'status', 'submitted_at',
        'last_edited_at', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'last_edited_at' => 'datetime',
    ];

    public function formRelease(): BelongsTo
    {
        return $this->belongsTo(FormRelease::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }
}
