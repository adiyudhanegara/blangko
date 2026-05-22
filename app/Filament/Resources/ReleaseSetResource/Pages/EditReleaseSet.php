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
                ->label(fn () => __('admin.action_publish'))
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->visible(fn () => $this->getRecord()->status === 'scheduled')
                ->requiresConfirmation()
                ->modalHeading(fn () => __('admin.modal_publish_set_heading'))
                ->modalDescription(fn () => __('admin.modal_publish_set_desc'))
                ->action(function (): void {
                    try {
                        ReleaseSetPublisher::publish($this->getRecord());
                        $this->refreshFormData(['status']);
                        Notification::make()->title(__('admin.notification_set_published'))->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title(__('admin.notification_publish_failed', ['message' => $e->getMessage()]))->danger()->send();
                    }
                }),

            Action::make('reopen')
                ->label(fn () => __('admin.action_reopen'))
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn () => $this->getRecord()->status === 'closed')
                ->requiresConfirmation()
                ->modalHeading(fn () => __('admin.modal_reopen_set_heading'))
                ->modalDescription(fn () => __('admin.modal_reopen_set_desc'))
                ->action(function (): void {
                    try {
                        ReleaseSetPublisher::reopen($this->getRecord());
                        $this->refreshFormData(['status']);
                        Notification::make()->title(__('admin.notification_set_reopened'))->success()->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title(__('admin.notification_publish_failed', ['message' => $e->getMessage()]))->danger()->send();
                    }
                }),

            Action::make('close')
                ->label(fn () => __('admin.action_close'))
                ->icon('heroicon-o-lock-closed')
                ->color('danger')
                ->visible(fn () => $this->getRecord()->status === 'open')
                ->requiresConfirmation()
                ->modalHeading(fn () => __('admin.modal_close_set_heading'))
                ->modalDescription(fn () => __('admin.modal_close_set_desc'))
                ->action(function (): void {
                    $this->getRecord()->update(['status' => 'closed']);
                    $this->refreshFormData(['status']);
                    Notification::make()->title(__('admin.notification_set_closed'))->success()->send();
                }),

            Action::make('public-link')
                ->label(fn () => __('admin.action_public_link'))
                ->icon('heroicon-o-link')
                ->color('info')
                ->url(fn () => route('release.show', $this->getRecord()->public_token), shouldOpenInNewTab: true),

            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $set = $this->getRecord()->fresh(['formReleases']);

        if ($set->status === 'open') {
            foreach ($set->formReleases as $release) {
                if ($release->published_at === null) {
                    ReleaseSetPublisher::snapshotRelease($release);
                }
            }
        }
    }
}
