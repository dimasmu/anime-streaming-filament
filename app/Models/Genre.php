<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Traits\HasRoles;

class Genre extends Model
{
    use HasFactory;
    use HasRoles;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
    ];

    public function animes(): BelongsToMany
    {
        return $this->belongsToMany(Anime::class);
    }
}
