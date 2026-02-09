<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Journal extends Model
{
    use HasFactory;

    protected $table = 'journal';

    protected $fillable = [
        'user_id',
        'content',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $journal): void {
            if (empty($journal->uuid)) {
                $journal->uuid = (string) Str::uuid();
            }
        });
    }
}
