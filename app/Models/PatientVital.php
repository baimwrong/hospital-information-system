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
        'news2_score',
        'news2_risk_level',
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
            'news2_score' => 'integer',
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

    /**
     * Compute the National Early Warning Score 2 (NEWS2) and clinical risk rating.
     *
     * @return array{score: int, risk_level: string, recommendation: string}
     */
    public static function computeNEWS2(
        ?int $respiratoryRate,
        ?int $oxygenSaturation,
        ?int $systolicBP,
        ?int $heartRate,
        ?float $temperature
    ): array {
        $score = 0;

        // 1. Respiratory Rate
        if ($respiratoryRate !== null) {
            if ($respiratoryRate <= 8 || $respiratoryRate >= 25) {
                $score += 3;
            } elseif ($respiratoryRate >= 21 && $respiratoryRate <= 24) {
                $score += 2;
            } elseif ($respiratoryRate >= 9 && $respiratoryRate <= 11) {
                $score += 1;
            }
        }

        // 2. Oxygen Saturation
        if ($oxygenSaturation !== null) {
            if ($oxygenSaturation <= 91) {
                $score += 3;
            } elseif ($oxygenSaturation <= 93) {
                $score += 2;
            } elseif ($oxygenSaturation <= 95) {
                $score += 1;
            }
        }

        // 3. Systolic Blood Pressure
        if ($systolicBP !== null) {
            if ($systolicBP <= 90 || $systolicBP >= 220) {
                $score += 3;
            } elseif ($systolicBP <= 100) {
                $score += 2;
            } elseif ($systolicBP <= 110) {
                $score += 1;
            }
        }

        // 4. Heart Rate
        if ($heartRate !== null) {
            if ($heartRate <= 40 || $heartRate >= 131) {
                $score += 3;
            } elseif ($heartRate >= 111) {
                $score += 2;
            } elseif ($heartRate <= 50 || $heartRate >= 91) {
                $score += 1;
            }
        }

        // 5. Temperature
        if ($temperature !== null) {
            if ($temperature <= 35.0) {
                $score += 3;
            } elseif ($temperature >= 39.1) {
                $score += 2;
            } elseif ($temperature <= 36.0 || $temperature >= 38.1) {
                $score += 1;
            }
        }

        if ($score >= 7) {
            $riskLevel = 'high';
            $recommendation = 'Critical clinical risk: Immediate emergency response required (ICU / Critical Care).';
        } elseif ($score >= 5) {
            $riskLevel = 'medium';
            $recommendation = 'Urgent clinical risk: Urgent response by medical team / senior physician.';
        } elseif ($score >= 1) {
            $riskLevel = 'low';
            $recommendation = 'Low clinical risk: Standard ward monitoring and routine vitals assessment.';
        } else {
            $riskLevel = 'normal';
            $recommendation = 'Normal baseline vitals: Routine clinical monitoring.';
        }

        return [
            'score' => $score,
            'risk_level' => $riskLevel,
            'recommendation' => $recommendation,
        ];
    }
}
