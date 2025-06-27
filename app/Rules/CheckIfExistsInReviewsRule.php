<?php

namespace App\Rules;

use App\Models\Reviewable;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfExistsInReviewsRule implements ValidationRule, DataAwareRule
{
    protected $data;

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $acceptableType = ['place', 'trip', 'event', 'volunteering', 'guideTrip', 'service', 'property'];

        if (!in_array($this->data['type'], $acceptableType)) {
            return;
        }
        // Validate if the type class has the method `findBySlug` before using it
        $modelClass = 'App\Models\\' . ucfirst($this->data['type']);
        $reviewableItem = $modelClass::findBySlug($value);

        if (!$reviewableItem) {
            $fail(__('validation.api.review-id-does-not-exists'));
            return;
        }

        $exists =  Reviewable::where('user_id', $userId)->where('reviewable_type', $modelClass)->where('reviewable_id', $reviewableItem?->id)->exists();
        if ($exists) {
            $fail(__('validation.api.you-already-make-review-for-this'));
        }
    }
}
