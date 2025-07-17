<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class Episode extends Model
{
    use HasFactory;
    use HasRoles;

    protected $fillable = [
        'anime_id',
        'title',
        'episode_number',
        'description',
        'thumbnail',
        'video_url',
        'video_upload_type_id',
        'duration',
        'air_date',
        'is_published',
        'likes',
        'views',
    ];

    protected $casts = [
        'air_date' => 'date',
        'is_published' => 'boolean',
    ];

    public function anime(): BelongsTo
    {
        return $this->belongsTo(Anime::class);
    }

    public function videoUploadType(): BelongsTo
    {
        return $this->belongsTo(VideoUploadType::class);
    }
}
