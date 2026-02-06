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
        'metadata',
        'verified',
    ];

    protected $casts = [
        'tags' => AsCollection::class,
        'metadata' => AsArrayObject::class,
    ];
}
