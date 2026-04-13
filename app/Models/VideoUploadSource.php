<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VideoUploadSource extends Model
{
    use HasFactory;

    protected $table = 'video_upload_sources';

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the episodes that belong to this video upload source.
     */
    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class, 'video_upload_source_id');
    }

    /**
     * Scope a query to only include active video upload sources.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include inactive video upload sources.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Get total views for all episodes using this source.
     */
    public function getTotalViewsAttribute(): int
    {
        return $this->episodes()->sum('views') ?? 0;
    }

    /**
     * Get total likes for all episodes using this source.
     */
    public function getTotalLikesAttribute(): int
    {
        return $this->episodes()->sum('likes') ?? 0;
    }

    /**
     * Get total watch time for all episodes using this source (in minutes).
     */
    public function getTotalWatchTimeAttribute(): int
    {
        return $this->episodes()
            ->join('watch_history', 'episodes.id', '=', 'watch_history.episode_id')
            ->sum('watch_history.watch_time_minutes') ?? 0;
    }

    /**
     * Get the count of episodes using this source.
     */
    public function getEpisodesCountAttribute(): int
    {
        return $this->episodes()->count();
    }
}
