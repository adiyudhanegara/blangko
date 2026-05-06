<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReleaseQuestion extends Model
{
    protected $fillable = [
        'form_release_id', 'original_question_id', 'type', 'label', 'help_text',
        'is_required', 'order', 'validation_rules', 'conditional_parent_id', 'conditional_value',
        'allow_duplicate_in_new_submission',
    ];

    protected $casts = [
        'is_required'                       => 'boolean',
        'validation_rules'                  => 'array',
        'allow_duplicate_in_new_submission' => 'boolean',
    ];

    public function formRelease(): BelongsTo
    {
        return $this->belongsTo(FormRelease::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ReleaseQuestionOption::class)->orderBy('order');
    }

    public function conditionalParent(): BelongsTo
    {
        return $this->belongsTo(ReleaseQuestion::class, 'conditional_parent_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(Answer::class);
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, ['radio', 'checkbox', 'select']);
    }
}
