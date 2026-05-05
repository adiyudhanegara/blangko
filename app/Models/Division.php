<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Division extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'slug', 'description'];

    protected static function booted(): void
    {
        static::creating(function (Division $division) {
            if (empty($division->slug)) {
                $division->slug = Str::slug($division->name);
            }
        });
    }

    public function participants(): HasMany
    {
        return $this->hasMany(Participant::class);
    }

    public function forms(): BelongsToMany
    {
        return $this->belongsToMany(Form::class, 'form_division');
    }

    public function formReleases(): BelongsToMany
    {
        return $this->belongsToMany(FormRelease::class, 'form_release_division');
    }
}
