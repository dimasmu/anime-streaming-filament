<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Bookmark extends Model
{
    protected $fillable = [
        'user_id',
        'anime_id',
        'episode_id',
        'notes',
        'timestamp',
    ];

    protected $casts = [
        'timestamp' => 'integer',
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

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForAnime($query, $animeId)
    {
        return $query->where('anime_id', $animeId);
    }

    public function scopeForEpisode($query, $episodeId)
    {
        return $query->where('episode_id', $episodeId);
    }
}
