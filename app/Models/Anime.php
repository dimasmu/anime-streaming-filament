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
        'slug',
        'description',
        'synopsis',
        'poster_image',
        'cover_image',
        'trailer_url',
        'video_upload_type_id',
        'status',
        'type',
        'episodes_count',
        'duration',
        'release_date',
        'rating',
        'studio_id',
        'source',
        'is_featured',
        'is_published',
    ];

    protected $casts = [
        'release_date' => 'date',
        'is_featured' => 'boolean',
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

    public function videoUploadType(): BelongsTo
    {
        return $this->belongsTo(VideoUploadType::class);
    }

    /**
     * Get the actual count of uploaded episodes
     */
    public function getActualEpisodesCountAttribute()
    {
        return $this->episodes()->count();
    }

    /**
     * Check if all planned episodes are uploaded
     */
    public function getIsCompleteAttribute()
    {
        return $this->episodes_count && $this->actual_episodes_count >= $this->episodes_count;
    }

    /**
     * Get the full URL for poster image
     */
    public function getPosterImageUrlAttribute()
    {
        return $this->poster_image ? asset('storage/' . $this->poster_image) : null;
    }

    /**
     * Get the full URL for cover image
     */
    public function getCoverImageUrlAttribute()
    {
        return $this->cover_image ? asset('storage/' . $this->cover_image) : null;
    }
}
