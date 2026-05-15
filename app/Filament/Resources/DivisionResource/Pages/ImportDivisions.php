<?php

namespace App\Filament\Resources\DivisionResource\Pages;

use App\Filament\Resources\DivisionResource;
use App\Services\DivisionImportService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ImportDivisions extends Page
{
    protected static string $resource = DivisionResource::class;

    protected string $view = 'filament.resources.division-resource.pages.import-divisions';

    public ?array $data = [];

    public array $importErrors = [];

    public int $importedCount = 0;

    public function getTitle(): string
    {
        return 'Import Divisions from Excel';
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Forms\Components\FileUpload::make('file')
                    ->label('Excel File (.xlsx)')
                    ->helperText('Download the blank template, fill it in, then upload here.')
                    ->acceptedFileTypes([
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/vnd.ms-excel',
                    ])
                    ->disk('local')
                    ->directory('division-imports')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();

        $relativePath = $data['file'];
        $absolutePath = Storage::disk('local')->path($relativePath);

        $result = (new DivisionImportService())->import($absolutePath);

        Storage::disk('local')->delete($relativePath);

        if (!empty($result['errors'])) {
            $this->importErrors   = $result['errors'];
            $this->importedCount  = 0;
            Notification::make()
                ->title('Import failed — ' . count($result['errors']) . ' error(s) found')
                ->danger()
                ->send();
            return;
        }

        $this->importErrors  = [];
        $this->importedCount = $result['count'];

        Notification::make()
            ->title("{$result['count']} division(s) imported successfully")
            ->success()
            ->send();

        $this->redirect(DivisionResource::getUrl('index'));
    }
}
