<?php

namespace App\Rules;

use App\Models\ServiceCategory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ChecIfServiceSubCategoryIsChildRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (ServiceCategory::whereNull('parent_id')->where('slug', $value)->exists()) {
            $fail(__('validation.api.invalid-subcategory-slug-not-child-category'));
        }
    }
}
