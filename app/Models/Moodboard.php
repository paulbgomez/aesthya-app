<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Moodboard extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'journal_id',
        'feeling',
        'artwork_ids',
        'music_ids',
        'video_ids',
        'book_ids',
        'generation_context'
    ];  

    protected $casts = [
        'artwork_ids' => AsCollection::class,
        'music_ids' => AsCollection::class,
        'video_ids' => AsCollection::class,
        'book_ids' => AsCollection::class,
        'generation_context' => AsArrayObject::class,
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
