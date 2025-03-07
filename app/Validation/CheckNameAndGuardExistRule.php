<?php
// app/Validation/CheckNameEnAndGuardExistRule.php

namespace App\Validation;

use Illuminate\Contracts\Validation\Rule;

use Spatie\Permission\Models\Permission;

// Replace YourModel with the appropriate model name

class CheckNameAndGuardExistRule implements Rule
{
    protected $currentPermissionId;

    public function __construct($currentPermissionId)
    {
        $this->currentPermissionId = $currentPermissionId;
    }
    public function passes($attribute, $value)
    {
        if($this->currentPermissionId){
            return !Permission::where('name', $value)->where('guard_name', request()->input('guard'))->where('id', '!=', $this->currentPermissionId) ->exists();
        }
        return Permission::where('name', $value)->where('guard_name', request()->input('guard'))->doesntExist();


    }

    public function message()
    {
        return 'The combination of name and guard already exists.';
    }
}
