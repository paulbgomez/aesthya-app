<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class MusicTrack extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'album',
        'genre',
        'duration',
        'audio_url',
        'metadata',
        'verified',
    ];

    protected $casts = [
        'metadata' => AsArrayObject::class,
    ];
}
