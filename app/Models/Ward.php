<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'floor',
        'capacity',
        'gender_preference',
        'daily_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function beds(): HasMany
    {
        return $this->hasMany(Bed::class);
    }

    public function availableBedsCount(): int
    {
        return $this->beds()->where('status', 'available')->count();
    }
}
