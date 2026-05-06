<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class ReleaseSet extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'description', 'period_label', 'public_token',
        'start_at', 'end_at', 'status', 'reminder_schedule', 'created_by',
    ];

    protected $casts = [
        'start_at'          => 'datetime',
        'end_at'            => 'datetime',
        'reminder_schedule' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (ReleaseSet $set) {
            if (empty($set->public_token)) {
                $set->public_token = Str::random(32);
            }
        });
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'release_set_division');
    }

    public function formReleases(): HasMany
    {
        return $this->hasMany(FormRelease::class)->orderBy('order');
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function getPublicUrlAttribute(): string
    {
        return route('release.show', $this->public_token);
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, (int) now()->diffInDays($this->end_at, false));
    }

    public function getSubmissionRateAttribute(): float
    {
        $total = $this->divisions()->withCount('participants')->get()->sum('participants_count');
        if ($total === 0) {
            return 0;
        }

        $completed = 0;
        foreach ($this->divisions as $division) {
            foreach ($division->participants as $participant) {
                if (app(\App\Services\CompletionCalculator::class)->isComplete($this, $participant)) {
                    $completed++;
                }
            }
        }

        return round($completed / $total * 100, 1);
    }
}
