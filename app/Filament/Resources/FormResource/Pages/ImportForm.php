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
        return __('admin.import_form_title');
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
                    ->label(fn () => __('admin.import_file_label'))
                    ->helperText(fn () => __('admin.import_file_helper'))
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
                ->title(__('admin.notif_form_failed', ['count' => count($result['errors'])]))
                ->danger()
                ->send();
            return;
        }

        $this->importErrors = [];

        Notification::make()
            ->title(__('admin.notif_form_imported'))
            ->success()
            ->send();

        $this->redirect(FormResource::getUrl('edit', ['record' => $result['form']->id]));
    }
}
