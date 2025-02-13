<?php

// app/Models/EvolutionEvent.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvolutionEvent extends Model
{
    use SoftDeletes;

    protected $fillable = ['data'];

    protected $casts = [
        'data' => 'object', // ou 'array', conforme sua preferência
    ];
}
