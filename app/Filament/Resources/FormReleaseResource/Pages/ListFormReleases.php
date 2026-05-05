<?php

namespace App\Filament\Resources\FormReleaseResource\Pages;

use App\Filament\Resources\FormReleaseResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFormReleases extends ListRecords
{
    protected static string $resource = FormReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
