<?php

namespace App\Filament\Resources\ReleaseSetResource\Pages;

use App\Filament\Resources\ReleaseSetResource;
use Filament\Resources\Pages\CreateRecord;

class CreateReleaseSet extends CreateRecord
{
    protected static string $resource = ReleaseSetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
