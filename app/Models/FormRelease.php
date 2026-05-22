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
        'start_at', 'end_at',
    ];

    protected $casts = [
        'published_at'             => 'datetime',
        'is_required'              => 'boolean',
        'min_submissions_required' => 'integer',
        'start_at'                 => 'datetime',
        'end_at'                   => 'datetime',
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

    /** Effective open date: own start_at if set, otherwise parent set's start_at. */
    public function getEffectiveStartAt(): ?\Illuminate\Support\Carbon
    {
        return $this->start_at ?? $this->releaseSet?->start_at;
    }

    /** Effective close date: own end_at if set, otherwise parent set's end_at. */
    public function getEffectiveEndAt(): ?\Illuminate\Support\Carbon
    {
        return $this->end_at ?? $this->releaseSet?->end_at;
    }

    /**
     * Admin-facing status for this form release.
     * published  — questions have been snapshotted
     * pending    — in an open set but not yet snapshotted (needs action)
     * unpublished — set not yet published
     */
    public function getAdminStatus(): string
    {
        if ($this->published_at !== null) {
            return 'published';
        }
        if ($this->releaseSet?->status === 'open') {
            return 'pending';
        }
        return 'unpublished';
    }

    /**
     * Participant-facing window state.
     * locked      — never snapshotted (shouldn't reach participant normally)
     * coming_soon — before effective start_at
     * form_closed — after effective end_at
     * open        — accepting responses
     */
    public function participantWindow(): string
    {
        if ($this->published_at === null) {
            return 'locked';
        }
        $now   = now();
        $start = $this->getEffectiveStartAt();
        $end   = $this->getEffectiveEndAt();
        if ($start && $now->lt($start)) {
            return 'coming_soon';
        }
        if ($end && $now->gt($end)) {
            return 'form_closed';
        }
        return 'open';
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
