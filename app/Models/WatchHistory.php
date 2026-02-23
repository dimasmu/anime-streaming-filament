<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WatchHistory extends Model
{
    protected $table = 'watch_history';

    protected $fillable = [
        'user_id',
        'anime_id',
        'episode_id',
        'current_position',
        'total_duration',
        'is_completed',
        'last_watched_at',
        'completed_at',
        'watch_time_minutes',
    ];

    protected $casts = [
        'current_position' => 'integer',
        'total_duration' => 'integer',
        'is_completed' => 'boolean',
        'last_watched_at' => 'datetime',
        'completed_at' => 'datetime',
        'watch_time_minutes' => 'integer',
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

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopeInProgress($query)
    {
        return $query->where('is_completed', false);
    }
}
