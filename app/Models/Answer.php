<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = [
        'submission_id', 'release_question_id', 'value', 'value_json',
        'file_path', 'file_original_name',
    ];

    protected $casts = [
        'value_json' => 'array',
    ];

    public function submission(): BelongsTo
    {
        return $this->belongsTo(Submission::class);
    }

    public function releaseQuestion(): BelongsTo
    {
        return $this->belongsTo(ReleaseQuestion::class);
    }

    public function getDisplayValueAttribute(): string
    {
        if ($this->value_json !== null) {
            return implode('; ', $this->value_json);
        }

        return (string) ($this->value ?? '');
    }
}
