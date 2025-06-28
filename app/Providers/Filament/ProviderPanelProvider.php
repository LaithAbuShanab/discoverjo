<?php

namespace App\Providers\Filament;

use App\Filament\Provider\Pages\Auth\RequestEmailVerificationNotification;
use App\Http\Middleware\RedirectIfNotFilamentAdmin;
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
use App\Filament\Provider\Pages\Auth\RequestPasswordReset;
use App\Filament\Provider\Pages\Dashboard;

class ProviderPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('provider')
            ->path('host')
            ->login(\App\Filament\Provider\Pages\CustomLogin::class)
            ->registration(\App\Filament\Provider\Pages\CustomRegister::class)
            ->emailVerification(RequestEmailVerificationNotification::class)
            ->passwordReset(RequestPasswordReset::class)
            ->favicon(asset('assets/images/logo_eyes_yellow.png'))
            ->brandName('Provider Panel')
            ->discoverResources(in: app_path('Filament/Provider/Resources'), for: 'App\\Filament\\Provider\\Resources')
            ->discoverPages(in: app_path('Filament/Provider/Pages'), for: 'App\\Filament\\Provider\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Provider/Widgets'), for: 'App\\Filament\\Provider\\Widgets')
            ->widgets([])
            ->plugins([FilamentTranslatableFieldsPlugin::make()->supportedLocales(['en' => 'English', 'ar' => 'العربية',]),])
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
                RedirectIfNotFilamentAdmin::class,
                Authenticate::class,
            ])
            ->authGuard('provider')
            ->databaseNotifications();
    }
}
