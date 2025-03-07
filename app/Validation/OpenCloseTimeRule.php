<?php
// app/Validation/CheckNameEnAndGuardExistRule.php

namespace App\Validation;

use Illuminate\Contracts\Validation\Rule;

use Spatie\Permission\Models\Role;

// Replace YourModel with the appropriate model name

class OpenCloseTimeRule implements Rule
{
    protected $dayOfWeek;

    public function __construct($dayOfWeek)
    {
        $this->dayOfWeek = $dayOfWeek;
    }

    public function passes($attribute, $value)
    {
        $index = array_search($this->dayOfWeek, array_column(request()->input('day_of_week'), 0));

        if ($index !== false && isset($value[$index]) && isset(request()->input('opening_hours')[$index]) && isset(request()->input('closing_hours')[$index])) {
            return $value[$index] === request()->input('opening_hours')[$index] && $value[$index] === request()->input('closing_hours')[$index];
        }

        return false;
    }

    public function message()
    {
        return 'The opening and closing hours must be the same for the selected day of the week.';
    }
}
