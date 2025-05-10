<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Models\Admin;
use App\Filament\Resources\AdminResource;
use App\Notifications\Admin\AdminUserNotification;
use Filament\Resources\Pages\CreateRecord;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterCreate(): void
    {
        $admin = Admin::find($this->record->id);

        $admin->must_reset_password = 1;
        $admin->save();

        if ($admin->email) {
            $admin->notify(new AdminUserNotification($admin, $this->data['password']));
        }
    }
}
