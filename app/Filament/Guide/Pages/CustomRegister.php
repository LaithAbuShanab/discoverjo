<?php

namespace App\Filament\Guide\Pages;

use App\Http\Responses\RegisterResponse;
use App\Models\User;
use App\Rules\CheckUserInBlackListRule;
use App\Rules\MinAgeRule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use LevelUp\Experience\Models\Activity;

class CustomRegister extends BaseRegister
{
    public function getMaxWidth(): string
    {
        return '5xl';
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->model(User::class)
                    ->schema([
                        Wizard::make([
                            Step::make(__('panel.guide.personal-information'))
                                ->schema([
                                    $this->getFirstNameField(),
                                    $this->getLastNameField(),
                                    $this->getUsernameField(),
                                    $this->getBirthdayField(),
                                    $this->getGenderField(),
                                ]),

                            Step::make(__('panel.guide.contact-information'))
                                ->schema([
                                    $this->getEmailField(),
                                    $this->getPhoneNumberField(),
                                ]),

                            Step::make(__('panel.guide.profile-details'))
                                ->schema([
                                    $this->getDescriptionField(),
                                    $this->getTagsField(),
                                    $this->getImageField(),
                                    $this->getProfessionalFileField(),
                                ]),

                            Step::make(__('panel.guide.security'))
                                ->schema([
                                    $this->getPasswordField(),
                                    $this->getPasswordConfirmationField(),
                                ]),
                        ])->submitAction(
                            \Filament\Forms\Components\Actions\Action::make('signUp')
                                ->label('Sign up')
                                ->submit('register')
                                ->color('primary')
                                ->button()
                                ->icon('heroicon-m-user-plus')
                        )
                    ])
                    ->statePath('data')
                    ->columns(1)
            ),
        ];
    }

    protected function getFirstNameField(): TextInput
    {
        return TextInput::make('first_name')
            ->label(__('panel.guide.first-name'))
            ->placeholder(__('panel.guide.enter-first-name'))
            ->required();
    }

    protected function getLastNameField(): TextInput
    {
        return TextInput::make('last_name')
            ->label(__('panel.guide.last-name'))
            ->placeholder(__('panel.guide.enter-last-name'))
            ->required();
    }

    protected function getUsernameField(): TextInput
    {
        return TextInput::make('username')
            ->nullable()
            ->minLength(3)
            ->maxLength(20)
            ->alphaDash()
            ->rule('regex:/^[a-zA-Z][a-zA-Z0-9_-]*$/')
            ->rule('not_regex:/\s/')
            ->label(__('panel.guide.username'))
            ->placeholder(__('panel.guide.enter-username'))
            ->required()
            ->unique(User::class, 'username');
    }

    protected function getBirthdayField(): DatePicker
    {
        return DatePicker::make('birthday')
            ->required()
            ->label(__('panel.guide.birthday'))
            ->rule(new MinAgeRule());
    }

    protected function getGenderField(): Select
    {
        return Select::make('sex')
            ->label(__('panel.guide.gender'))
            ->options([
                1 => 'Male',
                2 => 'Female',
            ])
            ->required()
            ->searchable()
            ->placeholder(__('panel.guide.select-gender'))
            ->rule(Rule::in([1, 2]));
    }

    protected function getEmailField(): TextInput
    {
        return TextInput::make('email')
            ->label(__('panel.guide.email'))
            ->placeholder(__('panel.guide.enter-email'))
            ->email()
            ->maxLength(255)
            ->required()
            ->unique(User::class, 'email')
            ->rule(new CheckUserInBlackListRule());
    }

    protected function getPhoneNumberField(): TextInput
    {
        return TextInput::make('phone_number')
            ->label(__('panel.guide.phone-number'))
            ->placeholder(__('panel.guide.enter-phone-number'))
            ->required();
    }

    protected function getDescriptionField(): Textarea
    {
        return Textarea::make('description')
            ->label(__('panel.guide.description'))
            ->placeholder(__('panel.guide.enter-description'))
            ->required();
    }

    protected function getTagsField(): Select
    {
        return Select::make('tags')
            ->label(__('panel.guide.tags'))
            ->relationship('tags', 'name')
            ->multiple()
            ->searchable()
            ->preload()
            ->required()
            ->placeholder(__('panel.guide.select-tags'))
            ->minItems(3);
    }

    protected function getImageField(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('image')
            ->label(__('panel.guide.avatar'))
            ->collection('avatar')
            ->disk('s3')
            ->openable()
            ->conversion('avatar_app');
    }

    protected function getProfessionalFileField(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('professional_file')
            ->label(__('panel.guide.professional-file'))
            ->collection('file')
            ->disk('s3')
            ->openable()
            ->conversion('file_preview');
    }

    protected function getPasswordField(): TextInput
    {
        return TextInput::make('password')
            ->password()
            ->required()
            ->confirmed()
            ->label(__('panel.guide.password'))
            ->placeholder(__('panel.guide.enter-password'))
            ->rule(Rules\Password::defaults());
    }

    protected function getPasswordConfirmationField(): TextInput
    {
        return TextInput::make('password_confirmation')
            ->password()
            ->label(__('panel.guide.password-confirmation'))
            ->placeholder(__('panel.guide.enter-password-confirmation'))
            ->required();
    }

    protected function getFormActions(): array
    {
        return [];
    }

    /**
     * @return ?RegistrationResponse
     */
    public function register(): ?RegistrationResponse
    {
        $data = $this->form->getState();

        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'birthday' => $data['birthday'],
            'sex' => $data['sex'],
            'email' => $data['email'],
            'phone_number' => $data['phone_number'],
            'description' => $data['description'],
            'password' => Hash::make($data['password']),
            'status' => 4,
            'type' => 2
        ]);

        if (!empty($this->data['tags'])) {
            $user->tags()->sync($this->data['tags']);
        }

        if (isset($this->data['image']) && !empty($this->data['image'])) {
            foreach ($this->data['image'] as $media) {
                $filename = Str::random(10) . '_' . time() . '.' . $media->getClientOriginalExtension();

                $user->addMedia($media->getRealPath())
                    ->usingFileName($filename)
                    ->toMediaCollection('avatar');
            }
        }

        if (isset($this->data['professional_file']) && $this->data['professional_file']) {
            foreach ($this->data['professional_file'] as $media) {
                $filename = Str::random(10) . '_' . time() . '.' . $media->getClientOriginalExtension();

                $user->addMedia($media->getRealPath())
                    ->usingFileName($filename)
                    ->toMediaCollection('file');
            }
        }

        $user->addPoints(10);
        $activity = Activity::find(1);
        $user->recordStreak($activity);
        $user->sendEmailVerificationNotification();

        return app(RegisterResponse::class);
    }
}
