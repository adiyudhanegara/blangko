<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormRelease extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'release_set_id', 'form_id', 'is_required', 'order',
        'min_submissions_required', 'published_at', 'created_by',
    ];

    protected $casts = [
        'published_at'             => 'datetime',
        'is_required'              => 'boolean',
        'min_submissions_required' => 'integer',
    ];

    public function releaseSet(): BelongsTo
    {
        return $this->belongsTo(ReleaseSet::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function releaseQuestions(): HasMany
    {
        return $this->hasMany(ReleaseQuestion::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /** Proxy: open if the parent ReleaseSet is open. */
    public function isOpen(): bool
    {
        return $this->releaseSet?->isOpen() ?? false;
    }

    public function getPublicUrlAttribute(): string
    {
        return $this->releaseSet?->public_url ?? '#';
    }

    /** Count of distinct participants who submitted at least once. */
    public function getSubmittedParticipantsCountAttribute(): int
    {
        return $this->submissions()
            ->where('status', 'submitted')
            ->distinct('participant_id')
            ->count('participant_id');
    }

    /**
     * Whether this release allows multiple submissions per participant
     * (delegates to the form setting).
     */
    public function allowsMultipleSubmissions(): bool
    {
        return (bool) $this->form?->allow_multiple_submissions;
    }
}
