<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class FormRelease extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'form_id', 'name', 'public_token', 'start_at', 'end_at',
        'status', 'reminder_schedule', 'published_at', 'created_by',
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'published_at' => 'datetime',
        'reminder_schedule' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (FormRelease $release) {
            if (empty($release->public_token)) {
                $release->public_token = Str::random(32);
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'form_release_division');
    }

    public function releaseQuestions(): HasMany
    {
        return $this->hasMany(ReleaseQuestion::class)->orderBy('order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
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

    public function getSubmissionRateAttribute(): float
    {
        $total = $this->divisions()->withCount('participants')->get()->sum('participants_count');
        if ($total === 0) {
            return 0;
        }

        return round($this->submissions()->where('status', 'submitted')->count() / $total * 100, 1);
    }
}
