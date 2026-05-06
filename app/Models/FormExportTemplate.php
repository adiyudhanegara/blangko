<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormExportTemplate extends Model
{
    protected $fillable = [
        'form_id', 'title_text', 'subtitle_template',
        'show_auto_number', 'auto_number_label',
        'participant_columns', 'column_order',
        'signature_role', 'signature_name', 'signature_nip', 'signature_position',
    ];

    protected $casts = [
        'show_auto_number'   => 'boolean',
        'participant_columns' => 'array',
        'column_order'       => 'array',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function resolveSubtitle(?string $periodLabel): string
    {
        return str_replace('{period_label}', $periodLabel ?? '', $this->subtitle_template ?? '');
    }

    /**
     * Default participant_columns structure used when none is configured.
     */
    public static function defaultParticipantColumns(): array
    {
        return [
            ['field' => 'name',       'label' => 'NAMA',        'enabled' => true],
            ['field' => 'division',   'label' => 'DIVISI',      'enabled' => false],
            ['field' => 'phone',      'label' => 'NO. HP',      'enabled' => false],
            ['field' => 'email',      'label' => 'EMAIL',       'enabled' => false],
            ['field' => 'identifier', 'label' => 'IDENTIFIER',  'enabled' => false],
        ];
    }
}
