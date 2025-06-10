<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

class UniquePriorityWithinParentService implements ValidationRule
{
    protected $parentId;
    protected $ignoreId;

    /**
     * Create a new rule instance.
     *
     * @param  int  $parentId
     * @param  int|null  $ignoreId
     * @return void
     */
    public function __construct($parentId, $ignoreId = null)
    {
        $this->parentId = $parentId;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Query to check if the priority already exists for the same parent_id
        $query = DB::table('service_categories')
            ->where('priority', $value)
            ->where('parent_id', $this->parentId);

        // Exclude the current category if updating
        if ($this->ignoreId) {
            $query->where('id', '<>', $this->ignoreId);
        }

        // Check if any record with the same priority exists
        if ($query->exists()) {
            $fail(__('validation.api.priority-must-be-unique-within-same-parent-category'));
        }
    }
}
