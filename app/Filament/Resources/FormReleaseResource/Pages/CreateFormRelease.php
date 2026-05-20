<?php

namespace App\Filament\Resources\FormReleaseResource\Pages;

use App\Filament\Resources\FormReleaseResource;
use App\Models\FormRelease;
use App\Services\ReleaseSetPublisher;
use Filament\Resources\Pages\CreateRecord;

class CreateFormRelease extends CreateRecord
{
    protected static string $resource = FormReleaseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var FormRelease $release */
        $release = $this->record;

        if ($release->releaseSet?->status === 'open' && $release->published_at === null) {
            ReleaseSetPublisher::snapshotRelease($release);
        }
    }
}
