<?php

namespace App\Filament\Resources\FormResource\Pages;

use App\Filament\Resources\FormResource;
use App\Services\FormImportService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ImportForm extends Page
{
    protected static string $resource = FormResource::class;

    protected string $view = 'filament.resources.form-resource.pages.import-form';

    public ?array $data = [];

    public array $importErrors = [];

    public function getTitle(): string
    {
        return 'Import Form from Excel';
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
                    ->directory('form-imports')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();

        $relativePath = $data['file'];
        $absolutePath = Storage::disk('local')->path($relativePath);

        $result = (new FormImportService())->import($absolutePath);

        Storage::disk('local')->delete($relativePath);

        if (!empty($result['errors'])) {
            $this->importErrors = $result['errors'];
            Notification::make()
                ->title('Import failed — ' . count($result['errors']) . ' error(s) found')
                ->danger()
                ->send();
            return;
        }

        $this->importErrors = [];

        Notification::make()
            ->title('Form imported successfully')
            ->success()
            ->send();

        $this->redirect(FormResource::getUrl('edit', ['record' => $result['form']->id]));
    }
}
