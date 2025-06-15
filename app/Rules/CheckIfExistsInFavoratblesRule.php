<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckIfExistsInFavoratblesRule implements ValidationRule, DataAwareRule
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

        if ($this->data['type'] == 'place') {
            if ($favorableItem->status != 1) {
                $fail(__('validation.api.the-selected-place-is-not-active'));
                return;
            }
        }


        $exists = DB::table('favorables')
            ->where('user_id', Auth::guard('api')->id())
            ->where('favorable_type', $modelClass)
            ->where('favorable_id', $favorableItem->id)
            ->exists();

        if ($exists) {
            $fail(__('validation.api.you-already-make-this-as-favorite'));
            return;
        }
    }
}
