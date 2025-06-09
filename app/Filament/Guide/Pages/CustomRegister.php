<?php

namespace App\Filament\Guide\Pages;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Pages\Auth\Register as BaseRegister;
use App\Models\User;
use App\Rules\CheckUserInBlackListRule;
use App\Rules\MinAgeRule;

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
                            Step::make('Personal Information')
                                ->schema([
                                    $this->getFirstNameField(),
                                    $this->getLastNameField(),
                                    $this->getUsernameField(),
                                    $this->getBirthdayField(),
                                    $this->getGenderField(),
                                ]),

                            Step::make('Contact Information')
                                ->schema([
                                    $this->getEmailField(),
                                    $this->getPhoneNumberField(),
                                ]),

                            Step::make('Profile Details')
                                ->schema([
                                    $this->getDescriptionField(),
                                    $this->getTagsField(),
                                    $this->getImageField(),
                                    $this->getProfessionalFileField(),
                                ]),

                            Step::make('Security')
                                ->schema([
                                    $this->getPasswordField(),
                                    $this->getPasswordConfirmationField(),
                                ]),
                        ])
                    ])
                    ->statePath('data')
                    ->columns(1)
            ),
        ];
    }

    protected function getFirstNameField(): TextInput
    {
        return TextInput::make('first_name')
            ->placeholder('Please Enter First Name')
            ->required();
    }

    protected function getLastNameField(): TextInput
    {
        return TextInput::make('last_name')
            ->placeholder('Please Enter Last Name')
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
            ->placeholder('Please Enter Username')
            ->required()
            ->unique(User::class, 'username');
    }

    protected function getBirthdayField(): DatePicker
    {
        return DatePicker::make('birthday')
            ->required()
            ->rule(new MinAgeRule());
    }

    protected function getGenderField(): Select
    {
        return Select::make('gender')
            ->options([
                1 => 'Male',
                2 => 'Female',
            ])
            ->required()
            ->searchable()
            ->placeholder('Please Select Gender')
            ->rule(Rule::in([1, 2]));
    }

    protected function getEmailField(): TextInput
    {
        return TextInput::make('email')
            ->email()
            ->maxLength(255)
            ->required()
            ->unique(User::class, 'email')
            ->placeholder('Please Enter Email')
            ->rule(new CheckUserInBlackListRule());
    }

    protected function getPhoneNumberField(): TextInput
    {
        return TextInput::make('phone_number')
            ->placeholder('Please Enter Phone Number')
            ->required();
    }

    protected function getDescriptionField(): Textarea
    {
        return Textarea::make('description')
            ->placeholder('Please Enter Description')
            ->required();
    }

    protected function getTagsField(): Select
    {
        return Select::make('tags')
            ->label('Tags')
            ->relationship('tags', 'name')
            ->multiple()
            ->searchable()
            ->preload()
            ->required()
            ->placeholder('Please select tags')
            ->minItems(3);
    }

    protected function getImageField(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('image')
            ->label('Guide Image')
            ->collection('avatar')
            ->disk('s3')
            ->openable()
            ->conversion('avatar_app');
    }

    protected function getProfessionalFileField(): SpatieMediaLibraryFileUpload
    {
        return SpatieMediaLibraryFileUpload::make('professional_file')
            ->label('Professional File')
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
            ->placeholder('Please Enter Password')
            ->rule(Rules\Password::defaults());
    }

    protected function getPasswordConfirmationField(): TextInput
    {
        return TextInput::make('password_confirmation')
            ->password()
            ->placeholder('Please Enter Password Confirmation')
            ->required();
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
            'gender' => $data['gender'],
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

        Auth::guard('guide')->login($user);

        return app(RegistrationResponse::class);
    }
}
