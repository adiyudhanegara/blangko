<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Answer extends Model
{
    protected $fillable = [
        'submission_id', 'release_question_id', 'value', 'value_json',
        'file_path', 'file_original_name', 'file_paths',
    ];

    protected $casts = [
        'value_json' => 'array',
        'file_paths' => 'array',
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
        /** @var array<mixed>|null $json */
        $json = $this->value_json;

        if ($json === null) {
            /** @var string|null $raw */
            $raw = $this->value;
            return (string) ($raw ?? '');
        }

        // Radio/select with "Other": {"option": "other", "other_text": "..."}
        if (isset($json['option'])) {
            return $json['option'] === 'other'
                ? (string) ($json['other_text'] ?? '')
                : (string) ($json['option'] ?? '');
        }

        // Checkbox with optional "Other": {"values": [...], "other_text": "..."}
        if (isset($json['values'])) {
            $values = array_filter((array) $json['values'], fn($v) => $v !== 'other');
            if (isset($json['other_text']) && $json['other_text'] !== '') {
                $values[] = $json['other_text'];
            }
            return implode('; ', $values);
        }

        // Plain array (legacy checkbox without Other support)
        return implode('; ', $json);
    }
}
