<?php

namespace App\Filament\Resources\ParticipantResource\Pages;

use App\Filament\Resources\ParticipantResource;
use App\Services\ParticipantImportService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Storage;

class ImportParticipants extends Page
{
    protected static string $resource = ParticipantResource::class;

    protected string $view = 'filament.resources.participant-resource.pages.import-participants';

    public ?array $data = [];

    public array $importErrors = [];

    public function getTitle(): string
    {
        return __('admin.import_participants_title');
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
                    ->directory('participant-imports')
                    ->required(),
            ])
            ->statePath('data');
    }

    public function import(): void
    {
        $data = $this->form->getState();

        $relativePath = $data['file'];
        $absolutePath = Storage::disk('local')->path($relativePath);

        $result = (new ParticipantImportService())->import($absolutePath);

        Storage::disk('local')->delete($relativePath);

        if ($result['count'] === 0 && !empty($result['errors'])) {
            $this->importErrors = $result['errors'];
            Notification::make()
                ->title(__('admin.notif_participants_failed', ['count' => count($result['errors'])]))
                ->danger()
                ->send();
            return;
        }

        $this->importErrors = $result['errors'];

        $title = !empty($result['errors'])
            ? __('admin.notif_participants_warnings', ['count' => $result['count'], 'warnings' => count($result['errors'])])
            : __('admin.notif_participants_imported', ['count' => $result['count']]);

        Notification::make()
            ->title($title)
            ->success()
            ->send();

        $this->redirect(ParticipantResource::getUrl('index'));
    }
}
