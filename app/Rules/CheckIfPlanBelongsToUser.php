<?php

namespace App\Rules;

use App\Models\Plan;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfPlanBelongsToUser implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $plan = Plan::where('slug', $value)->where('creator_type', 'App\Models\User')->where('creator_id', $userId)->exists();
        if (!$plan) {
            $fail(__('validation.api.you_are_not_the_owner_of_this_plan'));
        }
    }
}
