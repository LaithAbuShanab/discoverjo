<?php

namespace App\Providers\Filament;

use App\Filament\Guide\Pages\Auth\RequestEmailVerificationNotification;
use App\Filament\Guide\Pages\Auth\RequestPasswordReset;
use App\Filament\Guide\Pages\Dashboard;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
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


class GuidePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('guide')
            ->path('guide')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->login(\App\Filament\Guide\Pages\CustomLogin::class)
            ->registration(\App\Filament\Guide\Pages\CustomRegister::class)
//            ->emailVerification(RequestEmailVerificationNotification::class)
//            ->passwordReset(RequestPasswordReset::class)
            ->favicon(asset('assets/images/logo_eyes_yellow.png'))
            ->brandName('Guide Panel')
            ->discoverResources(in: app_path('Filament/Guide/Resources'), for: 'App\\Filament\\Guide\\Resources')
            ->discoverPages(in: app_path('Filament/Guide/Pages'), for: 'App\\Filament\\Guide\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Guide/Widgets'), for: 'App\\Filament\\Guide\\Widgets')
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
            ->authGuard('guide');
    }
}
