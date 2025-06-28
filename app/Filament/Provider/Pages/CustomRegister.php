<?php

namespace App\Filament\Provider\Pages;

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
                            Step::make(__('panel.provider.personal-information'))
                                ->schema([
                                    $this->getFirstNameField(),
                                    $this->getLastNameField(),
                                    $this->getUsernameField(),
                                    $this->getBirthdayField(),
                                    $this->getGenderField(),
                                    $this->getTypeField(),
                                ]),

                            Step::make(__('panel.provider.contact-information'))
                                ->schema([
                                    $this->getEmailField(),
                                    $this->getPhoneNumberField(),
                                ]),

                            Step::make(__('panel.provider.profile-details'))
                                ->schema([
                                    $this->getDescriptionField(),
                                    $this->getTagsField(),
                                    $this->getImageField(),
                                    $this->getProfessionalFileField(),
                                ]),

                            Step::make(__('panel.provider.security'))
                                ->schema([
                                    $this->getPasswordField(),
                                    $this->getPasswordConfirmationField(),
                                ]),
                        ])->submitAction(
                            \Filament\Forms\Components\Actions\Action::make('signUp')
                                ->label(__('panel.provider.register'))
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
            ->label(__('panel.provider.first-name'))
            ->placeholder(__('panel.provider.enter-first-name'))
            ->required();
    }


    protected function getLastNameField(): TextInput
    {
        return TextInput::make('last_name')
            ->label(__('panel.provider.last-name'))
            ->placeholder(__('panel.provider.enter-last-name'))
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
            ->label(__('panel.provider.username'))
            ->placeholder(__('panel.provider.enter-username'))
            ->required()
            ->unique(User::class, 'username');
    }

    protected function getBirthdayField(): DatePicker
    {
        return DatePicker::make('birthday')
            ->label(__('panel.provider.birthday'))
            ->required()
            ->rule(new MinAgeRule());
    }

    protected function getGenderField(): Select
    {
        return Select::make('sex')
            ->label(__('panel.provider.gender'))
            ->options([
                1 => __('app.male'),
                2 => __('app.female'),
            ])
            ->required()
            ->searchable()
            ->placeholder(__('panel.provider.select-gender'))
            ->rule(Rule::in([1, 2]));
    }

    protected function getTypeField(): Select
    {
        return Select::make('type')
            ->label(__('panel.provider.user-type'))
            ->options([
                2 => __('panel.provider.guide'),
                3 => __('panel.provider.provider'),
                4 => __('panel.provider.host'),
            ])
            ->multiple()
            ->required()
            ->searchable()
            ->placeholder(__('panel.provider.select-user-type'))
            ->rules([
                'required',
                'array',
                'min:1',
            ]);
    }

    protected function getEmailField(): TextInput
    {
        return TextInput::make('email')
            ->label(__('panel.provider.email'))
            ->email()
            ->maxLength(255)
            ->required()
            ->unique(User::class, 'email')
            ->placeholder(__('panel.provider.enter-email'))
            ->rule(new CheckUserInBlackListRule());
    }

    protected function getPhoneNumberField(): TextInput
    {
        return TextInput::make('phone_number')
            ->label(__('panel.provider.phone-number'))
            ->placeholder(__('panel.provider.enter-phone-number'))
            ->required();
    }

    protected function getDescriptionField(): Textarea
    {
        return Textarea::make('description')
            ->label(__('panel.provider.description'))
            ->placeholder(__('panel.provider.enter-description'))
            ->required();
    }

    protected function getTagsField(): Select
    {
        return Select::make('tags')
            ->label(__('panel.provider.tags'))
            ->relationship('tags', 'name')
            ->multiple()
            ->searchable()
            ->preload()
            ->required()
            ->placeholder(__('panel.provider.select-tags'))
            ->minItems(3);
    }

    protected function getImageField(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('image')
            ->label(__('panel.provider.avatar'))
            ->collection('avatar')
            ->disk('s3')
            ->openable()
            ->conversion('avatar_app')
            ->required();
    }

    protected function getProfessionalFileField(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('professional_file')
            ->label(__('panel.provider.professional-file'))
            ->collection('file')
            ->disk('s3')
            ->openable()
            ->conversion('file_preview')
            ->required();
    }

    protected function getPasswordField(): TextInput
    {
        return TextInput::make('password')
            ->label(__('panel.provider.password'))
            ->password()
            ->required()
            ->confirmed()
            ->placeholder(__('panel.provider.enter-password'))
            ->rule(Rules\Password::defaults());
    }

    protected function getPasswordConfirmationField(): TextInput
    {
        return TextInput::make('password_confirmation')
            ->label(__('panel.provider.password-confirmation'))
            ->password()
            ->placeholder(__('panel.provider.enter-password-confirmation'))
            ->required();
    }

    protected function getFormActions(): array
    {
        return [];
    }

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
        ]);

        if (!empty($this->data['tags'])) {
            $user->tags()->sync($this->data['tags']);
        }

        if (!empty($this->data['type'])) {
            foreach ($this->data['type'] as $type) {
                $user->userTypes()->create([
                    'type' => $type,
                ]);
            }
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
