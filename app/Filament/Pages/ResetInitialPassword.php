<?php

namespace App\Filament\Pages;

use App\Models\Admin;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Hash;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Password;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;

class ResetInitialPassword extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static string $view = 'filament.pages.reset-initial-password';

    public static function shouldRegisterNavigation(): bool
    {
        return false; // Hide from sidebar
    }

    public array $data = []; // Bind form inputs

    public function mount(): void
    {
        // Optional: redirect if not flagged for reset
        if (! auth()->guard('admin')->user()?->must_reset_password) {
            $this->redirect('/admin'); // Or use Filament::getUrl()
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath('data'); // Bind to $data
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make('Reset Your Password')
                ->description('To continue, please set a new secure password.')
                ->schema([
                    Grid::make(2)->schema([
                        Forms\Components\TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->required()
                            ->confirmed()
                            ->revealable()
                            ->rule(Password::default()),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->required()
                            ->revealable(),
                    ]),
                ])
                ->columns(1)
                ->collapsible(), // Optional: make it collapsible
        ];
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Update Password')
                ->submit('submit')
                ->color('warning')
                ->button()
                ->extraAttributes(['class' => 'text-sm px-4 py-2']), // optional design tweak
        ];
    }

    public function submit(): void
    {
        $this->validate(); // Runs rules from form schema

       $admin = Admin::find(auth()->guard('admin')->user()->id);
       $admin->password = Hash::make($this->data['password']);
       $admin->must_reset_password = false;
       $admin->save();

        $this->redirect('/admin');
    }
}
