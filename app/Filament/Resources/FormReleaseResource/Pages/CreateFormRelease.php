<?php

namespace App\Filament\Resources\FormReleaseResource\Pages;

use App\Filament\Resources\FormReleaseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFormRelease extends CreateRecord
{
    protected static string $resource = FormReleaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
