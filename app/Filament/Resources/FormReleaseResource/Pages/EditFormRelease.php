<?php

namespace App\Filament\Resources\FormReleaseResource\Pages;

use App\Filament\Resources\FormReleaseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFormRelease extends EditRecord
{
    protected static string $resource = FormReleaseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
