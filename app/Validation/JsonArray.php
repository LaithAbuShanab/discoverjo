<?php
// app/Validation/CheckNameEnAndGuardExistRule.php

namespace App\Validation;

use App\Models\Category;
use Illuminate\Contracts\Validation\Rule;

use Spatie\Permission\Models\Permission;

// Replace YourModel with the appropriate model name

class JsonArray implements Rule
{

    public function passes($attribute, $value)
    {
        // Decode the JSON string into an array
        $decodedValue = json_decode($value, true);

        // Check if the decoded value is an array
        return is_array($decodedValue);
    }

    public function message()
    {
        return 'The :attribute must be a valid JSON array.';
    }
}
