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
        return 'Import Participants from Excel';
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
                ->title('Import failed — ' . count($result['errors']) . ' error(s) found')
                ->danger()
                ->send();
            return;
        }

        $this->importErrors = $result['errors']; // non-fatal warnings (e.g. division not found)

        $message = "{$result['count']} participant(s) imported successfully";
        if (!empty($result['errors'])) {
            $message .= ' with ' . count($result['errors']) . ' warning(s)';
        }

        Notification::make()
            ->title($message)
            ->success()
            ->send();

        $this->redirect(ParticipantResource::getUrl('index'));
    }
}
