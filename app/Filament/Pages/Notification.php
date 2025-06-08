<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Notifications\Users\SendNotificationFromAdmin;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\CheckboxList;
use Filament\Pages\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Notification as FacadesNotification;
use Illuminate\Validation\ValidationException;

class Notification extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationGroup = 'Send Notifications';
    protected static string $view = 'filament.pages.notification';

    public ?array $titleName = null;
    public ?array $body = null;
    public array $selectedUsers = [];
    public array $selectedGuideUsers = [];

    /**
     * Fill form on mount
     */
    public function mount(): void
    {
        $this->form->fill();
    }

    /**
     * Validation rules
     */
    protected function rules(): array
    {
        return [
            'titleName' => ['required', 'array', 'max:255'],
            'body' => ['required', 'array'],
        ];
    }

    /**
     * Filament form definition
     */
    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Section::make('Basic Information')
                        ->description('Write the notification title and message you want to send.')
                        ->schema([
                            TextInput::make('titleName')
                                ->label('Notification Title')
                                ->placeholder('Please Enter Notification Title')
                                ->required()
                                ->translatable(),

                            Textarea::make('body')
                                ->label('Notification Body')
                                ->placeholder('Please Enter Notification Body')
                                ->rows(10)
                                ->required()
                                ->translatable()
                        ])
                        ->columnSpan(1),

                    Grid::make(1)->schema([
                        Tabs::make('NotificationTabs')
                            ->tabs([
                                Tabs\Tab::make('Users')
                                    ->schema([
                                        CheckboxList::make('selectedUsers')
                                            ->label('')
                                            ->options(
                                                User::where('type', '==', 1)
                                                    ->pluck('username', 'id')
                                                    ->toArray()
                                            )
                                            ->bulkToggleable()
                                            ->columns(3),
                                    ]),
                                Tabs\Tab::make('Guide Users')
                                    ->schema([
                                        CheckboxList::make('selectedGuideUsers')
                                            ->label('')
                                            ->options(
                                                User::where('type', 1)
                                                    ->pluck('username', 'id')
                                                    ->toArray()
                                            )
                                            ->bulkToggleable()
                                            ->columns(3),
                                    ]),
                            ]),
                    ])->columnSpan(1),
                ]),
            ])
            ->statePath(null);
    }

    /**
     * Page action: Send notification
     */
    public function getActions(): array
    {
        return [
            Action::make('send')
                ->label('Send Notification')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->requiresConfirmation()
                ->action(function (): void {
                    $this->validate();

                    $title_en = $this->titleName['en'];
                    $title_ar = $this->titleName['ar'];
                    $body_en = $this->body['en'];
                    $body_ar = $this->body['ar'];

                    $title = ['en' => $title_en, 'ar' => $title_ar];
                    $body = ['en' => $body_en, 'ar' => $body_ar];


                    $users = $this->selectedUsers ?? [];
                    $guideUsers = $this->selectedGuideUsers ?? [];

                    if (empty($users) && empty($guideUsers)) {
                        throw ValidationException::withMessages([
                            'selectedUsers' => 'Please select at least one user or guide to notify.',
                            'selectedGuideUsers' => 'Please select at least one user or guide to notify.',
                        ]);
                    }

                    $userIds = array_unique(array_merge($users, $guideUsers));
                    $userModels = User::whereIn('id', $userIds)->get();

                    // Send via Laravel Notifications
                    FacadesNotification::send($userModels, new SendNotificationFromAdmin($title, $body));

                    // Send via Firebase if tokens exist
                    foreach ($userModels as $user) {
                        if ($user->DeviceTokenMany && $user->DeviceTokenMany->isNotEmpty()) {
                            $receiverLanguage = $user->lang;
                            $notificationData = [
                                'title' => $title[$receiverLanguage],
                                'body' => $body[$receiverLanguage],
                                'icon' => asset('assets/icon/new.png'),
                                'sound' => 'default',
                            ];

                            // Collect all tokens for this user
                            $tokens = $user->DeviceTokenMany->pluck('token')->toArray();
                            sendNotification($tokens, $notificationData);
                        }
                    }


                    FilamentNotification::make()
                        ->success()
                        ->title('Notification Sent')
                        ->body('Your notification has been delivered successfully.')
                        ->send();

                    $this->titleName = null;
                    $this->body = null;
                    $this->selectedUsers = [];
                    $this->selectedGuideUsers = [];
                }),
        ];
    }
}
