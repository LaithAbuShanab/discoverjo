<?php

namespace App\Filament\Host\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Filament\Pages\Auth\Login as BaseLogin;


class CustomLogin  extends BaseLogin
{
    protected function getEmailFormComponent(): \Filament\Forms\Components\Component
    {
        // Rename the field to a generic 'login' that accepts email or username
        return \Filament\Forms\Components\TextInput::make('login')
            ->label(__('panel.guide.username-email'))
            ->required()
            ->autocomplete('username');
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        $login = $data['login'] ?? '';

        $field = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        return [
            $field => $login,
            'password' => $data['password'],
        ];
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.login' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    public function authenticate(): ?LoginResponse
    {
        $credentials = $this->getCredentialsFromFormData($this->form->getState());

        $field = array_keys($credentials)[0];
        $login = $credentials[$field];
        $password = $credentials['password'];

        $user = User::where($field, $login)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            $this->throwFailureValidationException();
        }

        // Check if user is a guide
        if ($user->type !== 4) {
            throw ValidationException::withMessages([
                'data.login' => __('panel.host.only-host-can-access'),
            ]);
        }

        // Status checks
        if ($user->status == 3) {
            throw ValidationException::withMessages([
                'data.login' => __('panel.guide.your-account-is-blocked'),
            ]);
        }

        if ($user->status == 4) {
            throw ValidationException::withMessages([
                'data.login' => __('panel.guide.your-account-is-suspended'),
            ]);
        }

        // ✅ Activate user and sync trip statuses if needed
        if ($user->status == 0) {
            $user->status = 1;
            $user->save();

            $now = now('Asia/Riyadh');

            $userTrip = \App\Models\Trip::where('user_id', $user->id)->latest()->first();
            if ($userTrip) {
                if ($userTrip->date_time > $now && $userTrip->status == 4) {
                    $userTrip->status = 1;
                    $userTrip->save();
                    \App\Models\UsersTrip::where('trip_id', $userTrip->id)
                        ->where('status', 5)
                        ->update(['status' => 1]);
                }
            }

            \App\Models\UsersTrip::where('user_id', $user->id)
                ->where('status', 5)
                ->update(['status' => 1]);

            $guideTrip = \App\Models\GuideTrip::where('guide_id', $user->id)->latest()->first();
            if ($guideTrip) {
                if ($guideTrip->start_datetime > $now && $guideTrip->status == 4) {
                    $guideTrip->status = 1;
                    $guideTrip->save();
                    \App\Models\GuideTripUser::where('guide_trip_id', $guideTrip->id)
                        ->where('status', 5)
                        ->update(['status' => 1]);
                }
            }

            \App\Models\GuideTripUser::where('user_id', $user->id)
                ->where('status', 5)
                ->update(['status' => 1]);

            //implement the process of api about service
        }

        Filament::auth()->login($user, $this->form->getState()['remember'] ?? false);


        return app(LoginResponse::class);
    }
}
