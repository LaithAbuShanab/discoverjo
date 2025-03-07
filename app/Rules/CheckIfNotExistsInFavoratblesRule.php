<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckIfNotExistsInFavoratblesRule implements ValidationRule
{
    protected $favarable_type;

    public function __construct($favarable_type)
    {
        $this->favarable_type= $favarable_type;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = DB::table('favorables')
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('favorable_type', $this->favarable_type)
            ->where('favorable_id', $value)
            ->exists();

        if (!$exists && $this->favarable_type::find($value) ) {
            $fail(__('app.event.api.this_is_not_in_favorite_list_to_delete'));
        }
    }
}
