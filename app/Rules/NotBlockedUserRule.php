<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class NotBlockedUserRule implements ValidationRule
{
    protected string $modelClass;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $currentUser = Auth::guard('api')->user();

        if (! $currentUser || ! class_exists($this->modelClass)) {
            $fail(__('validation.api.generic-action-denied'));
            return;
        }

        $record = $this->modelClass::find($value);

        if (! $record || ! isset($record->user_id)) {
            $fail(__('validation.api.generic-action-denied'));
            return;
        }

        $ownerId = $record->user_id;

        if (
            $currentUser->blockedUsers->contains('id', $ownerId) ||
            $currentUser->blockers->contains('id', $ownerId)
        ) {
            $fail(__('validation.api.generic-action-denied'));
        }
    }
}
