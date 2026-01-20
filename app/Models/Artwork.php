<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class Artwork extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'image_url',
        'source',
        'style',
        'color_palette',
        'themes',
        'metadata'
    ];

    protected $casts = [
        'color_palette' => AsCollection::class,
        'themes' => AsCollection::class,
        'metadata' => AsArrayObject::class,
    ];

    public function favorites()
    {
        return $this->hasMany(UserFavorite::class);
    }

    public function isFavoritedBy($userId)
    {
        return $this->favorites()->where('user_id', $userId)->exists();
    }
}