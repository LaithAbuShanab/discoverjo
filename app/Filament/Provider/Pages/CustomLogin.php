<?php

namespace App\Filament\Provider\Pages;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    protected function getEmailFormComponent(): \Filament\Forms\Components\Component
    {
        // Rename the field to a generic 'login' that accepts email or username
        return \Filament\Forms\Components\TextInput::make('login')
            ->label(__('panel.provider.username-email'))
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
        if (! $user->userTypes()->whereIn('type', [2, 3, 4])->exists()) {
            throw ValidationException::withMessages([
                'data.login' => 'Only Host users can access this panel.',
            ]);
        }

        // Status checks
        if ($user->status == 3) {
            throw ValidationException::withMessages([
                'data.login' => 'Your account is blocked',
            ]);
        }

        if ($user->status == 4) {
            throw ValidationException::withMessages([
                'data.login' => 'Your account is waiting for admin approval.',
            ]);
        }

        // ✅ Activate user and sync trip statuses if needed
        if ($user->status == 0) {
            $user->status = 1;
            $user->save();
        }

        Filament::auth()->login($user, $this->form->getState()['remember'] ?? false);


        return app(LoginResponse::class);
    }
}
