<?php
// app/Validation/CheckNameEnAndGuardExistRule.php

namespace App\Validation;

use App\Models\Category;
use Illuminate\Contracts\Validation\Rule;

use Spatie\Permission\Models\Permission;

// Replace YourModel with the appropriate model name

class CategoryExistsRule implements Rule
{

    public function passes($attribute, $value)
    {
        // Check if the category with the provided ID exists
        return Category::where('id', $value)->exists();
    }

    public function message()
    {
        return 'The selected category does not exist.';
    }
}
