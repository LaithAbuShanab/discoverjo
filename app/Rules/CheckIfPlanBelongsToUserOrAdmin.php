<?php

namespace App\Rules;

use App\Models\Plan;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfPlanBelongsToUserOrAdmin implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $plan = Plan::where('id', $value)->where('creator_type', 'App\Models\User')->where('creator_id', $userId)->exists();
        $adminPlan = Plan::where('id', $value)->where('creator_type', 'App\Models\Admin')->exists();
        if (!$plan && !$adminPlan) {
            $fail(__('validation.api.you_are_not_the_owner_of_this_plan'));
        }
    }
}
