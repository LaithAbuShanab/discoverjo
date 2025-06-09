<?php

namespace App\Filament\Guide\Pages;

use App\Models\User;
use App\Rules\CheckTagExistsRule;
use App\Rules\CheckUserInBlackListRule;
use App\Rules\MinAgeRule;
use Filament\Pages\Page;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Validation\Rule;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\FileUpload;
use Filament\Pages\Auth\Register;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


class CustomRegister extends BaseRegister
{
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        $this->getFirstNameField(),
                        $this->getLastNameField(),
                        $this->getUsernameField(),
                        $this->getBirthdayField(),
                        $this->getGenderField(),
                        $this->getEmailField(),
                        $this->getPhoneNumberField(),
                        $this->getDescriptionField(),
                        $this->getTagsField(),
                        $this->getImageField(),
                        $this->getProfessionalFileField(),
                        $this->getDeviceTokenField(),
                        $this->getPasswordField(),
                        $this->getPasswordConfirmationField(),
                    ])
                    ->statePath('data')
            ),
        ];
    }

    protected function getFirstNameField(): TextInput
    {
        return TextInput::make('first_name')
            ->required();
    }

    protected function getLastNameField(): TextInput
    {
        return TextInput::make('last_name')
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
            ->rule(Rule::in([1, 2]));
    }

    protected function getEmailField(): TextInput
    {
        return TextInput::make('email')
            ->email()
            ->maxLength(255)
            ->required()
            ->unique(User::class, 'email')
            ->rule(new CheckUserInBlackListRule());
    }

    protected function getPhoneNumberField(): TextInput
    {
        return TextInput::make('phone_number')
            ->required();
    }

    protected function getDescriptionField(): Textarea
    {
        return Textarea::make('description')
            ->required();
    }

    protected function getTagsField(): TagsInput
    {
        return TagsInput::make('tags')
            ->required()
            ->rule(new CheckTagExistsRule());
    }

    protected function getImageField(): FileUpload
    {
        return FileUpload::make('image')
            ->image()
            ->required();
    }

    protected function getProfessionalFileField(): FileUpload
    {
        return FileUpload::make('professional_file')
            ->required()
            ->acceptedFileTypes([
                'application/pdf',
                'image/jpeg',
                'image/png',
                'image/jpg',
                'image/gif',
                'image/svg+xml',
                'image/webp',
                'image/bmp',
                'image/tiff',
                'image/x-icon',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            ]);
    }

    protected function getDeviceTokenField(): TextInput
    {
        return TextInput::make('device_token')
            ->required()
            ->maxLength(255);
    }

    protected function getPasswordField(): TextInput
    {
        return TextInput::make('password')
            ->password()
            ->required()
            ->confirmed()
            ->rule(Rules\Password::defaults());
    }

    protected function getPasswordConfirmationField(): TextInput
    {
        return TextInput::make('password_confirmation')
            ->password()
            ->required();
    }

}


