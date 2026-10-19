<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'request_number',
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'clinical_notes',
        'priority',
        'status',
        'technician_id',
        'collected_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'collected_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabTestResult::class);
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "LAB-{$year}-";

        $last = static::where('request_number', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(request_number) DESC, request_number DESC')
            ->value('request_number');

        $nextSeq = 1;
        if ($last && preg_match('/LAB-\d{4}-(\d+)/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return sprintf('LAB-%s-%04d', $year, $nextSeq);
    }
}
