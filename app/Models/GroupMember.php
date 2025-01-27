<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = ['conversation_id', 'user_id', 'joined_datetime', 'left_datetime'];
    protected $table = 'group_members';

    public function conversation() : BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function user() : BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
