<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DifferentQuestionsRule implements ValidationRule
{
    protected $current_question;
    protected $next_question_id;

    public function __construct($current_question, $next_question_id)
    {
        $this->current_question = $current_question;
        $this->next_question_id = $next_question_id;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if ($this->current_question == $this->next_question_id) {
            $fail(__('validation.api.the-current-question-and-next-question-cannot-be-the-same'));
        }
    }
}
