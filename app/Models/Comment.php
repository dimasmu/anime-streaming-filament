<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'user_id',
        'anime_id',
        'episode_id',
        'content',
        'is_spoiler',
        'is_visible',
    ];

    protected $casts = [
        'is_spoiler' => 'boolean',
        'is_visible' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    public function episode(): BelongsTo
    {
        return $this->belongsTo(Episode::class);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopeForAnime($query, $animeId)
    {
        return $query->where('anime_id', $animeId);
    }

    public function scopeForEpisode($query, $episodeId)
    {
        return $query->where('episode_id', $episodeId);
    }

    public function scopeWithoutSpoilers($query)
    {
        return $query->where('is_spoiler', false);
    }
}
