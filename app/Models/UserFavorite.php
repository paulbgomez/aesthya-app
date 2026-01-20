<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Model;

class UserFavorite extends Model
{
    protected $fillable = [
        'user_id',
        'artwork_id',
        'moodboard_id',
        'feeling',
        'context'
    ];

    protected $casts = [
        'context' => AsArrayObject::class
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function artwork()
    {
        return $this->belongsTo(Artwork::class);
    }

    public function moodboard()
    {
        return $this->belongsTo(Moodboard::class);
    }
}
