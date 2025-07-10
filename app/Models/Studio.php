<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Permission\Traits\HasRoles;

class Studio extends Model
{
    use HasFactory;
    use HasRoles;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'founded_year',
        'logo',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function animes(): HasMany
    {
        return $this->hasMany(Anime::class);
    }
}
