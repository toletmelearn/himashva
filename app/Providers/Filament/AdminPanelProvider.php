<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use Filament\FontProviders\GoogleFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
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
            ->login(Login::class)
            ->brandName('Himashva')
            ->brandLogo(null) // text logo via CSS, see resources/views/filament/custom-admin-styles.blade.php
            ->favicon(asset('favicon.ico'))
            ->darkMode() // enabled with a user toggle (not forced)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('280px')
            ->maxContentWidth('full')
            ->databaseNotifications()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->colors([
                'primary' => Color::hex('#8B5E3C'),
                'gray' => Color::hex('#6B5D50'),
                'danger' => Color::Red,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
            ])
            ->font('Inter', provider: GoogleFontProvider::class)
            ->navigationGroups([
                NavigationGroup::make('Dashboard')
                    ->icon('heroicon-o-home')
                    ->collapsed(false),
                NavigationGroup::make('Catalog')
                    ->icon('heroicon-o-shopping-bag')
                    ->collapsed(false),
                NavigationGroup::make('Sales')
                    ->icon('heroicon-o-banknotes')
                    ->collapsed(false),
                NavigationGroup::make('CMS')
                    ->icon('heroicon-o-document-text')
                    ->collapsed(true),
                NavigationGroup::make('Administration')
                    ->icon('heroicon-o-shield-check')
                    ->collapsed(true),
                NavigationGroup::make('Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(true),
            ])
            ->renderHook(
                'panels::head.end',
                fn () => view('filament.custom-admin-styles')
            )
            ->renderHook(
                'panels::body.end',
                fn () => view('filament.custom-admin-scripts')
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
