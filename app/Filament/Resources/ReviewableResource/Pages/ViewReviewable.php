<?php

namespace App\Filament\Resources\ReviewableResource\Pages;

use App\Filament\Resources\ReviewableResource;
use App\Models\DeleteCounter;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Notifications\Notification as FilamentNotification;

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

                    handleWarning($this->record);

                    FilamentNotification::make()
                        ->title('New Warning Sent Successfully')
                        ->body('A new warning has been issued to the user.')
                        ->success()
                        ->send();

                })->disabled(function ($record) {
                    $existsForRecord = DeleteCounter::where([
                        'typeable_type' => get_class($record),
                        'typeable_id'   => $record->id,
                        'user_id'       => $record->user_id,
                    ])->exists();

                    $userHasFourDeletions = DeleteCounter::where('user_id', $record->user_id)
                        ->latest()
                        ->value('deleted_count') === 4;

                    $userInactive = $record->user && $record->user->status == 0;

                    return $existsForRecord || $userHasFourDeletions || $userInactive;
                })

        ];
    }
}
