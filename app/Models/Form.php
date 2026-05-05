<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Form extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'description', 'language', 'status', 'allow_edit_after_submit', 'created_by',
    ];

    protected $casts = [
        'allow_edit_after_submit' => 'boolean',
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
}
