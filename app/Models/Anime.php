<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class Anime extends Model
{
    use HasFactory;
    use HasRoles;

    protected $fillable = [
        'title',
        'japanese_title',
        'slug',
        'description',
        'synopsis',
        'poster_image',
        'cover_image',
        'banner',
        'trailer_url',
        'status',
        'type',
        'episodes_count',
        'sub_episodes',
        'dub_episodes',
        'duration',
        'release_date',
        'release_year',
        'rating',
        'quality',
        'studio_id',
        'source',
        'is_featured',
        'is_adult',
        'is_published',
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_featured' => 'boolean',
        'is_adult' => 'boolean',
        'is_published' => 'boolean',
        'rating' => 'decimal:1',
    ];

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function studio(): BelongsTo
    {
        return $this->belongsTo(Studio::class);
    }

    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_favorites');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeAdult($query, $includeAdult = false)
    {
        return $includeAdult ? $query : $query->where('is_adult', false);
    }

    public function scopeByQuality($query, $quality)
    {
        return $query->where('quality', $quality);
    }

    public function scopeHasSub($query)
    {
        return $query->where('sub_episodes', '>', 0);
    }

    public function scopeHasDub($query)
    {
        return $query->where('dub_episodes', '>', 0);
    }
}
