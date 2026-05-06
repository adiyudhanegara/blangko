<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    protected $fillable = [
        'form_id', 'type', 'label', 'help_text', 'is_required', 'order',
        'validation_rules', 'conditional_parent_id', 'conditional_value',
        'allow_duplicate_in_new_submission',
    ];

    protected $casts = [
        'is_required'                       => 'boolean',
        'validation_rules'                  => 'array',
        'allow_duplicate_in_new_submission' => 'boolean',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function options(): HasMany
    {
        return $this->hasMany(QuestionOption::class)->orderBy('order');
    }

    public function conditionalParent(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'conditional_parent_id');
    }

    public function conditionalChildren(): HasMany
    {
        return $this->hasMany(Question::class, 'conditional_parent_id');
    }

    public function hasOptions(): bool
    {
        return in_array($this->type, ['radio', 'checkbox', 'select']);
    }
}
