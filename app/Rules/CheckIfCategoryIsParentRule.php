<?php

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfCategoryIsParentRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Category::whereNotNull('parent_id')->where('slug', $value)->exists()) {
            $fail(__('validation.api.invalid-category-id-not-main-category'));
        }
    }
}
