<?php

namespace App\Rules;

use App\Models\Reviewable;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfNotExistsInReviewsRule implements ValidationRule, DataAwareRule
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
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $userId = Auth::guard('api')->user()->id;
        $acceptableType = ['place', 'trip', 'event', 'volunteering', 'guideTrip','service'];

        if (!in_array($this->data['type'], $acceptableType)) {
            return;
        }
        // Validate if the type class has the method `findBySlug` before using it
        $modelClass = 'App\Models\\' . ucfirst($this->data['type']);
        $favorableItem = $modelClass::findBySlug($value);

        if (!$favorableItem) {
            $fail(__('validation.api.review-id-does-not-exists'));
            return;
        }

        $exists = Reviewable::where('user_id', $userId)->where('reviewable_type', $modelClass)->where('reviewable_id', $favorableItem?->id)->exists();
        if (!$exists) {
            $fail(__('validation.api.you-did-not-make-review-for-this'));
        }
    }
}
