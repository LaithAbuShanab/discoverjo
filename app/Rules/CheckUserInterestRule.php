<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckUserInterestRule implements ValidationRule
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

        if ($exists) {
            $fail(__('validation.api.you-already-make-this-as-interest'));
        }

        $datetime = $this->interestable_type::find($value)?->end_datetime;
        $now = now()->setTimezone('Asia/Riyadh');

        if($datetime && $datetime < $now){
            $fail(__('validation.api.you-can\'t-make-this-as-interest-because-it-in-the-past'));
        }

    }
}
