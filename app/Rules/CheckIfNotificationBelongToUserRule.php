<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Notifications\DatabaseNotification;

class CheckIfNotificationBelongToUserRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $notification = DatabaseNotification::where('id', $value)
            ->where('notifiable_id', auth()->id())
            ->where('notifiable_type', get_class(auth()->user()))
            ->first();
        if(!$notification){
            $fail(__('validation.api.this-notification-did-not-belong-to-this-user'));
        }
    }
}
