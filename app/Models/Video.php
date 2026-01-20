<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'title',
        'channel',
        'video_id',
        'thumbnail',
        'youtube_url',
        'tags',
        'metadata'
    ];

    protected $casts = [
        'tags' => AsCollection::class,
        'metadata' => AsArrayObject::class,
    ];
}
