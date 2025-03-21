<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class DeleteCounter extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function typeable(): MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'typeable_type', 'typeable_id');
    }
}
