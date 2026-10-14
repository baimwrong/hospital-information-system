<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bed extends Model
{
    use HasFactory;

    protected $fillable = [
        'ward_id',
        'bed_number',
        'bed_type',
        'status',
        'daily_rate',
    ];

    protected function casts(): array
    {
        return [
            'daily_rate' => 'decimal:2',
        ];
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class);
    }

    public function currentAdmission(): HasOne
    {
        return $this->hasOne(Admission::class)->where('status', 'admitted');
    }

    public function getEffectiveRateAttribute(): float
    {
        return (float) ($this->daily_rate ?? $this->ward->daily_rate ?? 0.00);
    }
}
