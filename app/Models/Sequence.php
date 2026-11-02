<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sequence extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'current_value',
    ];

    protected function casts(): array
    {
        return [
            'current_value' => 'integer',
        ];
    }
}
