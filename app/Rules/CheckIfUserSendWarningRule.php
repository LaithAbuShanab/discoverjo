<?php

namespace App\Rules;

use App\Models\User;
use App\Models\Warning;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfUserSendWarningRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()?->id;
        $ReportedUser = User::findBySlug($value);
        if(!$ReportedUser)return;
        $exists = Warning::where('reporter_id',$userId)->where('reported_id',$ReportedUser->id)->exists();
        if($exists) {
            $fail(__('validation.api.you-already-make-report-for-this-user'));
        }
    }
}
