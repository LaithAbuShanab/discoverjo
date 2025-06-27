<?php

namespace App\Providers\Filament;

use App\Filament\Host\Pages\CustomLogin;
use App\Filament\Host\Pages\CustomRegister;
use App\Filament\Host\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Outerweb\FilamentTranslatableFields\Filament\Plugins\FilamentTranslatableFieldsPlugin;

class HostPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('host')
            ->path('host')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->brandName('DISCOVER JO')
            ->login(CustomLogin::class)
            ->registration(CustomRegister::class)
            ->discoverResources(in: app_path('Filament/Host/Resources'), for: 'App\\Filament\\Host\\Resources')
            ->discoverPages(in: app_path('Filament/Host/Pages'), for: 'App\\Filament\\Host\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Host/Widgets'), for: 'App\\Filament\\Host\\Widgets')
            ->widgets([])
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
            ->plugins([

                FilamentTranslatableFieldsPlugin::make()
                    ->supportedLocales([
                        'en' => 'English',
                        'ar' => 'العربية',
                    ]),
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('host');
    }
}
