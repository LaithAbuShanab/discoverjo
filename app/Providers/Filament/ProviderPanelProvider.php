<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Outerweb\FilamentTranslatableFields\Filament\Plugins\FilamentTranslatableFieldsPlugin;

class ProviderPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('provider')
            ->path('provider')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->login(\App\Filament\Provider\Pages\CustomLogin::class)
            ->registration(\App\Filament\Provider\Pages\CustomRegister::class)
            ->emailVerification()
            ->passwordReset()
            ->brandName('Provider Panel')
            ->discoverResources(in: app_path('Filament/Provider/Resources'), for: 'App\\Filament\\Provider\\Resources')
            ->discoverPages(in: app_path('Filament/Provider/Pages'), for: 'App\\Filament\\Provider\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Provider/Widgets'), for: 'App\\Filament\\Provider\\Widgets')
            ->widgets([])
            ->plugins([

                FilamentTranslatableFieldsPlugin::make()
                    ->supportedLocales([
                        'en' => 'English',
                        'ar' => 'العربية',
                    ]),
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
            ])
            ->authGuard('provider');
    }
}
