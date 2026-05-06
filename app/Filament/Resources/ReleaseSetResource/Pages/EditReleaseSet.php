<?php

namespace App\Filament\Resources\ReleaseSetResource\Pages;

use App\Filament\Resources\ReleaseSetResource;
use App\Services\ReleaseSetPublisher;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditReleaseSet extends EditRecord
{
    protected static string $resource = ReleaseSetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('publish')
                ->label('Publish')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->visible(fn () => $this->getRecord()->status === 'scheduled')
                ->requiresConfirmation()
                ->action(function (): void {
                    try {
                        ReleaseSetPublisher::publish($this->getRecord());
                        $this->refreshFormData(['status']);
                        Notification::make()->title('Release set published.')->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Publish failed: ' . $e->getMessage())->danger()->send();
                    }
                }),

            Action::make('close')
                ->label('Close')
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->visible(fn () => $this->getRecord()->status === 'open')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->getRecord()->update(['status' => 'closed']);
                    $this->refreshFormData(['status']);
                    Notification::make()->title('Release set closed.')->success()->send();
                }),

            Action::make('public-link')
                ->label('Public Link')
                ->icon('heroicon-o-link')
                ->color('info')
                ->url(fn () => route('release.show', $this->getRecord()->public_token), shouldOpenInNewTab: true),

            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
