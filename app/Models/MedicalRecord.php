<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class MedicalRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'encounter_number',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'chief_complaint',
        'history_of_present_illness',
        'physical_examination',
        'diagnosis_primary',
        'diagnosis_icd10_code',
        'diagnosis_secondary',
        'treatment_plan',
        'doctor_notes',
        'follow_up_date',
    ];

    protected function casts(): array
    {
        return [
            'follow_up_date' => 'date',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescription(): HasOne
    {
        return $this->hasOne(Prescription::class);
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class);
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "ENC-{$year}-";

        $last = static::withTrashed()
            ->where('encounter_number', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(encounter_number) DESC, encounter_number DESC')
            ->value('encounter_number');

        $nextSeq = 1;
        if ($last && preg_match('/ENC-\d{4}-(\d+)/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return sprintf('ENC-%s-%04d', $year, $nextSeq);
    }
}
