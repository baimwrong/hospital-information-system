<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_code',
        'name',
        'category',
        'price',
        'sample_type',
        'reference_range',
        'turnaround_time_hours',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'turnaround_time_hours' => 'integer',
        ];
    }

    public function testResults(): HasMany
    {
        return $this->hasMany(LabTestResult::class);
    }
}
