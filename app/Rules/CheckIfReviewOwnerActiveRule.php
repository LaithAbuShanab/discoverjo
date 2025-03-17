<?php

namespace App\Rules;

use App\Models\Reviewable;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfReviewOwnerActiveRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $review = Reviewable::find($value);
        if(!$review) return;
        if(!$review->user->status){
            $fail(__('validation.api.the-review-creator-not-longer-active'));
            return;
        }
    }
}
