<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Poem extends Model
{
    protected $fillable = ['name', 'author', 'content'];

    protected function casts(): array
    {
        return [
            'content' => 'array',
        ];
    }
}
