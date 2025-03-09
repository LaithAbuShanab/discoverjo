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
        // Ensure the value is a string and convert it to an array using explode
        $tagsSlugs = explode(',', $value);

        // Ensure at least three tags are selected
        if (count($tagsSlugs) < 3) {
            $fail(__('validation.api.select_at_least_three_tags'));
            return;
        }

        // Validate that all tags exist in the database
        foreach ($tagsSlugs as $slug) {
            if (!Tag::where('slug', $slug)->exists()) {
                $fail(__('validation.api.tag_does_not_exist', ['tag' => $slug]));
                return;
            }
        }
    }
}
