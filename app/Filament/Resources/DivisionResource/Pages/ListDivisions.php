<?php

namespace App\Filament\Resources\DivisionResource\Pages;

use App\Filament\Resources\DivisionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDivisions extends ListRecords
{
    protected static string $resource = DivisionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label(fn () => __('admin.import_from_excel'))
                ->icon('heroicon-o-arrow-up-tray')
                ->color('gray')
                ->url(DivisionResource::getUrl('import')),
            Actions\CreateAction::make(),
        ];
    }
}
