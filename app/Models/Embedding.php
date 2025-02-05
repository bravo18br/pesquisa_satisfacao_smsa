<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Embedding extends Model
{
    protected $fillable = ['content', 'embedding'];

    protected $casts = [
        'embedding' => Vector::class,
    ];
}
