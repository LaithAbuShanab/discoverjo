<?php

namespace App\Rules;

use App\Models\QuestionChain;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueCombinationRule implements ValidationRule
{
    protected $question_id;
    protected $answer;
    protected $next_question_id;
    protected $type;

    public function __construct($question_id, $answer, $next_question_id, $type)
    {
        $this->question_id = $question_id;
        $this->answer = $answer;
        $this->next_question_id = $next_question_id;
        $this->type = $type;
    }

    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        if ($this->type == 'update') {
            if (QuestionChain::where('question_id', '!=', $this->question_id)->where('answer', $this->answer)->where('next_question_id', $this->next_question_id)->exists()) {
                $fail(__('validation.api.combination-of-question-answer-and-next-question-must-be-unique'));            }
        } else {
            if (QuestionChain::where('question_id', $this->question_id)->where('answer', $this->answer)->where('next_question_id', $this->next_question_id)->exists()) {
                $fail(__('validation.api.combination-of-question-answer-and-next-question-must-be-unique'));            }
        }
    }
}
