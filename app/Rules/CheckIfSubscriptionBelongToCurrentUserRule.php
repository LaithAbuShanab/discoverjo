<?php

namespace App\Rules;

use App\Models\GuideTripUser;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfSubscriptionBelongToCurrentUserRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $exists = GuideTripUser::where('id', $value)->where('user_id', $userId)->exists();
        if (!$exists) {
            $fail(__('validation.api.this-record-not-belong-to-you'));
        }
    }
}
