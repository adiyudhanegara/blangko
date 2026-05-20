<?php

namespace App\Providers\Filament;

use App\Filament\Widgets\ActiveReleasesWidget;
use App\Filament\Widgets\PendingAssignmentsWidget;
use App\Filament\Widgets\StatsOverviewWidget;
use App\Filament\Widgets\SubmissionTrendWidget;
use App\Http\Middleware\SetLocale;
use Filament\Actions\Action;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('Blangko')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
                StatsOverviewWidget::class,
                ActiveReleasesWidget::class,
                PendingAssignmentsWidget::class,
                SubmissionTrendWidget::class,
            ])
            ->navigationGroups([
                NavigationGroup::make(fn () => __('admin.nav_form_management')),
                NavigationGroup::make(fn () => __('admin.nav_participants')),
                NavigationGroup::make(fn () => __('admin.nav_settings'))
                    ->collapsed(),
            ])
            ->userMenuItems([
                Action::make('lang_id')
                    ->label(fn () => __('admin.switch_to_id'))
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('lang.switch', 'id'))
                    ->color(fn () => app()->getLocale() === 'id' ? 'primary' : 'gray'),
                Action::make('lang_en')
                    ->label(fn () => __('admin.switch_to_en'))
                    ->icon('heroicon-o-language')
                    ->url(fn () => route('lang.switch', 'en'))
                    ->color(fn () => app()->getLocale() === 'en' ? 'primary' : 'gray'),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware([SetLocale::class], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
