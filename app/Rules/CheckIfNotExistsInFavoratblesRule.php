<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckIfNotExistsInFavoratblesRule implements ValidationRule, DataAwareRule
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
        $acceptableType = ['place', 'trip', 'event', 'volunteering', 'plan', 'guideTrip','service'];

        if (!in_array($this->data['type'], $acceptableType)) {
            return;
        }
        // Validate if the type class has the method `findBySlug` before using it
        $modelClass = 'App\Models\\' . ucfirst($this->data['type']);

        $favorableItem = $modelClass::findBySlug($value);

        if (!$favorableItem) {
            $fail(__('validation.api.favorite-id-does-not-exists'));
            return;
        }
        $exists = DB::table('favorables')
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('favorable_type', $modelClass)
            ->where('favorable_id', $favorableItem->id)
            ->exists();

        if (!$exists) {
            $fail(__('validation.api.this_is_not_in_favorite_list'));
        }
    }
}
