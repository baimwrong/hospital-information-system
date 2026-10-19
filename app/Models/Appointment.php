<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_number',
        'patient_id',
        'doctor_id',
        'department_id',
        'appointment_date',
        'appointment_time',
        'priority',
        'status',
        'reason_for_visit',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function vitals(): HasOne
    {
        return $this->hasOne(PatientVital::class);
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class);
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "APT-{$year}-";

        $last = static::where('appointment_number', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(appointment_number) DESC, appointment_number DESC')
            ->value('appointment_number');

        $nextSeq = 1;
        if ($last && preg_match('/APT-\d{4}-(\d+)/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return sprintf('APT-%s-%04d', $year, $nextSeq);
    }
}
