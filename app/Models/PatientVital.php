<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientVital extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'recorded_by_id',
        'appointment_id',
        'temperature',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'heart_rate',
        'respiratory_rate',
        'oxygen_saturation',
        'weight',
        'height',
        'bmi',
        'triage_level',
        'clinical_notes',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'temperature' => 'decimal:1',
            'weight' => 'decimal:2',
            'height' => 'decimal:2',
            'bmi' => 'decimal:1',
            'recorded_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Compute BMI given weight (kg) and height (cm).
     */
    public static function computeBMI(?float $weight, ?float $height): ?float
    {
        if ($weight && $height && $height > 0) {
            $heightMeters = $height / 100;

            return round($weight / ($heightMeters * $heightMeters), 1);
        }

        return null;
    }
}
