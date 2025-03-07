<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckUserInterestExistsRule implements ValidationRule
{
    protected $interestable_type;

    public function __construct($interestable_type)
    {
        $this->interestable_type = $interestable_type;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = DB::table('interestables')
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('interestable_type', $this->interestable_type)
            ->where('interestable_id', $value)
            ->exists();

        if (!$exists && $this->interestable_type::find($value) ) {
            $fail(__('validation.api.you-didn\'t-make-this-to-interest-to-delete-interest'));
        }

    }
}
