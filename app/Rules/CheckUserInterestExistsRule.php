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
        $type =  $this->interestable_type::findBySlug($value);
        if (!$type) {
            return;
        }
        $exists = DB::table('interestables')
            ->where('user_id', Auth::guard('api')->user()->id)
            ->where('interestable_type', $this->interestable_type)
            ->where('interestable_id', $type->id)
            ->exists();

        if (!$exists) {
            $fail(__('validation.api.you-did-not-make-this-to-interest-to-delete-interest'));
        }
    }
}
