<?php

namespace App\Rules;

use App\Models\GuideTripUser;
use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class CheckIfThereIsDublicateRule implements ValidationRule, DataAwareRule
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

        $firstName = $this->data['first_name'] ?? '';
        $lastName = $this->data['last_name'] ?? '';

        $exists = GuideTripUser::where('user_id', $userId)
            ->where('first_name', $firstName)
            ->where('last_name', $lastName)
            ->exists();

        if ($exists) {
            $fail('A subscription with this full name already exists.');
        }
    }

}

