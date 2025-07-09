<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Anime extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'synopsis',
        'poster_image',
        'cover_image',
        'trailer_url',
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
}