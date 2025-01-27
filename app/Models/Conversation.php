<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = ['trip_id'];
    protected $table = 'conversations';

    public function members() : HasMany
    {
        return $this->hasMany(GroupMember::class, 'conversation_id');
    }

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function messages() : HasMany
    {
        return $this->hasMany(Message::class, 'conversation_id');
    }

    public function lastMessage() : HasMany
    {
        return $this->hasOne(Message::class, 'conversation_id')->latest('updated_at');
    }
}
