<?php

namespace App\Filament\Resources\ReviewableResource\Pages;

use App\Filament\Resources\ReviewableResource;
use App\Models\DeleteCounter;
use App\Notifications\Users\Warning\NewWarningUserNotification;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Lang;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Auth;

class ViewReviewable extends ViewRecord
{
    protected static string $resource = ReviewableResource::class;

    /**
     * Define custom header actions for the view page.
     *
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('warning')
                ->label('Send Warning')
                ->icon('heroicon-o-exclamation-triangle')
                ->color('warning')
                ->requiresConfirmation()
                ->action(function (): void {
                    $record = $this->record;

                    if (!$record->user || $record->user->status !== 1) {
                        return;
                    }

                    DeleteCounter::create([
                        'typeable_type' => get_class($record),
                        'typeable_id'   => $record->id,
                        'user_id'       => $record->user_id,
                        'deleted_count' => 1,
                    ]);

                    $totalWarnings = DeleteCounter::where('user_id', $record->user_id)
                        ->sum('deleted_count');

                    $user = $record->user;
                    $issuer = Auth::user();
                    $deviceToken = optional($user->deviceToken)->token;
                    $receiverLanguage = in_array($user->lang, ['en', 'ar']) ? $user->lang : 'en';

                    // Step 3: If user has 3 or more warnings, block
                    if ($totalWarnings >= 3) {
                        $user->status = 0;
                        $user->save();

                        // Send database notification (blocked)
                        Notification::send($user, new NewWarningUserNotification('blocked'));

                        // Push notification
                        if ($deviceToken) {
                            $notificationData = [
                                'title' => Lang::get('app.notifications.new-blocked-two-weeks-title', [], $receiverLanguage),
                                'body'  => Lang::get('app.notifications.new-blocked-two-weeks-body', ['username' => $user->username], $receiverLanguage),
                                'sound' => 'default',
                            ];

                            sendNotification([$deviceToken], $notificationData);
                        }
                    } else {
                        // Send database notification (warning)
                        Notification::send($user, new NewWarningUserNotification('warning'));

                        // Push notification
                        if ($deviceToken) {
                            $notificationData = [
                                'title' => Lang::get('app.notifications.new-warning-title', [], $receiverLanguage),
                                'body'  => Lang::get('app.notifications.new-warning-body', ['username' => $user->username], $receiverLanguage),
                                'sound' => 'default',
                            ];

                            sendNotification([$deviceToken], $notificationData);
                        }
                    }

                    // Step 4: Notify admin (in Filament panel)
                    FilamentNotification::make()
                        ->title('New Warning Sent Successfully')
                        ->body('A new warning has been issued to the user.')
                        ->success()
                        ->send();
                })
                ->disabled(fn() => DeleteCounter::where([
                    'typeable_type' => get_class($this->record),
                    'typeable_id'   => $this->record->id,
                    'user_id'       => $this->record->user_id,
                ])->exists()),
        ];
    }
}
