<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckUserExistsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Ensure the value is a string and convert it to an array using explode
        $usersSlugs = explode(',', $value);

        // Validate that all tags exist in the database
        foreach ($usersSlugs as $slug) {
            if (!User::where('slug', $slug)->where('status',1)->exists()) {
                $fail(__('validation.api.user_does_not_exist', ['user' => $slug]));
                return;
            }
        }
    }
}
