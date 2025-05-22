<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CheckIfHasInjectionBasedTimeRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // ✅ Step 1: If value is null or empty, skip validation
        if (is_null($value) || trim($value) === '') {
            return;
        }

        // ✅ Step 2: Define dangerous keywords
        $dangerousWords = [
            'sleep',
            'dbms_lock.sleep',
            'dbms_pipe.receive_message',
            'utl_inaddr.get_host_address',
            'utl_inaddr.get_host_name',
            'utl_http.request',
            'utl_tcp.connect',
            'utl_smtp.open_connection',
            'pg_sleep',
            'pg_sleep_for',
            'pg_sleep_until',
            'benchmark',
            'waitfor delay',
            'waitfor time',
            'case',
            'when',
            'then',
            'else',
            'if',
            'begin',
            'end',
            'exec',
            'execute',
            'declare',
            'dual',
            'sysdate',
            'systimestamp',
            'current_timestamp',
            'now',
            'select',
            'from',
            'union',
            'where',
            'and',
            'or',
            'null',
            'is',
            'not',
            'exists',
            'having',
            'cast',
            'convert',
            'create',
            'drop',
            'insert',
            'update',
            'delete',
            'alter',
        ];

        // ✅ Step 3: Normalize the input for comparison
        $lowerValue = strtolower(trim((string) $value));

        // ✅ Step 4: Check if any dangerous word exists in the input
        foreach ($dangerousWords as $word) {
            if (str_contains($lowerValue, $word)) {
                $fail("invalid input");
                return;
            }
        }
    }
}
