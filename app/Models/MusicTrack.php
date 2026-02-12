<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class MusicTrack extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'preview_url',
        'album_art',
        'genres',
        'mood_tags',
        'metadata',
    ];

    protected $casts = [
        'metadata' => AsArrayObject::class,
        'genres' => AsArrayObject::class,
        'mood_tags' => AsArrayObject::class,
    ];
}
