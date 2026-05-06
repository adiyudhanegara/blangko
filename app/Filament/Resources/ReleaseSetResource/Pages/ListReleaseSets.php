<?php

namespace App\Filament\Resources\ReleaseSetResource\Pages;

use App\Filament\Resources\ReleaseSetResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReleaseSets extends ListRecords
{
    protected static string $resource = ReleaseSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
