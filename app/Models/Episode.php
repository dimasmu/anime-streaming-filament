<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'anime_id',
        'title',
        'episode_number',
        'description',
        'thumbnail',
        'video_url',
        'duration',
        'air_date',
        'is_published',
    ];

    protected $casts = [
        'air_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }
}