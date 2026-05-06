<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReleaseQuestionOption extends Model
{
    protected $fillable = ['release_question_id', 'label', 'value', 'order', 'is_other'];

    protected $casts = ['is_other' => 'boolean'];

    public function releaseQuestion(): BelongsTo
    {
        return $this->belongsTo(ReleaseQuestion::class);
    }
}
