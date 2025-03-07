<?php

namespace App\Rules;

use App\Models\Tag;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckTagExistsRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Decode the JSON string into an array
        $decodedValue = json_decode($value, true);

        // Check if decoding was successful
        if (json_last_error() !== JSON_ERROR_NONE) {
            $fail(__('validation.api.invalid_json_format'));
            return;
        }

        // Count the number of tags
        $countTags = count($decodedValue);
        if ($countTags < 3) {
            $fail(__('validation.api.select_at_least_three_tags'));
            return;
        }

        // Check if each tag exists
        foreach ($decodedValue as $tag) {
            if (!Tag::where('id', $tag)->exists()) {
                $fail(__('validation.api.tag_does_not_exist'));
                return;
            }
        }
    }

}
