<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvolutionEvent extends Model
{
    use SoftDeletes;

    protected $fillable =
    [
        // 'event',
        // 'instance',
        'data'];
    protected $dates = ['deleted_at'];
    protected $casts = [
        'data' => 'json'
    ];
}
