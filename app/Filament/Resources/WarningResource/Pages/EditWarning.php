<?php

namespace App\Filament\Resources\WarningResource\Pages;

use App\Filament\Resources\WarningResource;
use App\Models\User;
use App\Models\Warning;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\DB;

class EditWarning extends EditRecord
{
    protected static string $resource = WarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $warning = $this->record;
        $userId = $warning->reported_id;
        $recordsCount = Warning::where('reported_id',$userId)->where('status',1)->count();
        $user = User::find($userId);
        if ($recordsCount == 4) {
            $user->status = 3;
            $user->save();
            DB::table('blocked_users')->insert([
                'email' => $user->email,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if($recordsCount == 4){
            $user->status = 3;
            $user->save();
        }

    }

}
