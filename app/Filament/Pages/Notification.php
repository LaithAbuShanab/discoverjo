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
use App\Jobs\SendUserNotificationJob;

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
                                                User::where('type', 1)
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
                                                User::where('type', 2)
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

                    $title = [
                        'en' => $this->titleName['en'],
                        'ar' => $this->titleName['ar'],
                    ];
                    $body = [
                        'en' => $this->body['en'],
                        'ar' => $this->body['ar'],
                    ];

                    $users = $this->selectedUsers ?? [];
                    $guideUsers = $this->selectedGuideUsers ?? [];

                    if (empty($users) && empty($guideUsers)) {
                        throw ValidationException::withMessages([
                            'selectedUsers' => 'Please select at least one user or guide to notify.',
                            'selectedGuideUsers' => 'Please select at least one user or guide to notify.',
                        ]);
                    }

                    $userIds = array_unique(array_merge($users, $guideUsers));

                    // ✅ Dispatch job to queue
                    SendUserNotificationJob::dispatch($title, $body, $userIds);

                    FilamentNotification::make()
                        ->success()
                        ->title('Notification Queued')
                        ->body('Your notification has been queued for background delivery.')
                        ->send();

                    $this->titleName = null;
                    $this->body = null;
                    $this->selectedUsers = [];
                    $this->selectedGuideUsers = [];
                })
        ];
    }
}
