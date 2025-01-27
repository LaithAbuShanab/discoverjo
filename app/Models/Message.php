<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

class Message extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['user_id', 'message_txt', 'conversation_id', 'sent_datetime'];
    protected $table = 'messages';

    protected $touches = ['conversation'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class, 'conversation_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('file')
            ->registerMediaConversions(function (Media $media) {
                $this->addMediaConversion('file_thumb')->width(450)->height(450)->format('webp')->nonQueued();;
            });
    }
}
