<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'author',
        'cover_image',
        'isbn',
        'tags',
        'metadata'
    ];

    protected $casts = [
        'tags' => AsCollection::class,
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
