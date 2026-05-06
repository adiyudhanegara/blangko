<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'language', 'status',
        'allow_edit_after_submit', 'allow_multiple_submissions',
        'allow_duplicate_from_previous', 'preview_question_ids', 'created_by',
    ];

    protected $casts = [
        'allow_edit_after_submit'       => 'boolean',
        'allow_multiple_submissions'    => 'boolean',
        'allow_duplicate_from_previous' => 'boolean',
        'preview_question_ids'          => 'array',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function divisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'form_division');
    }

    public function releases(): HasMany
    {
        return $this->hasMany(FormRelease::class);
    }

    public function exportTemplate(): HasOne
    {
        return $this->hasOne(FormExportTemplate::class);
    }

    /**
     * Returns the questions to show as preview columns on the submission history page.
     * Falls back to the first 3 non-file, non-textarea questions.
     */
    public function previewQuestions(): \Illuminate\Database\Eloquent\Collection
    {
        if (!empty($this->preview_question_ids)) {
            return $this->questions()->whereIn('id', $this->preview_question_ids)->get()
                ->sortBy(fn($q) => array_search($q->id, $this->preview_question_ids))
                ->values();
        }

        return $this->questions()
            ->whereNotIn('type', ['file', 'textarea'])
            ->limit(3)
            ->get();
    }
}
