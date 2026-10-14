<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'mrn',
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'blood_group',
        'genotype',
        'national_id',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'allergies',
        'chronic_diseases',
        'insurance_provider',
        'insurance_policy_number',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'allergies' => 'array',
            'chronic_diseases' => 'array',
        ];
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAgeAttribute(): int
    {
        return $this->date_of_birth ? Carbon::parse($this->date_of_birth)->age : 0;
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function vitals(): HasMany
    {
        return $this->hasMany(PatientVital::class)->latest('recorded_at');
    }

    public function medicalRecords(): HasMany
    {
        return $this->hasMany(MedicalRecord::class)->latest();
    }

    public function admissions(): HasMany
    {
        return $this->hasMany(Admission::class)->latest('admission_date');
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class)->latest();
    }

    public function labRequests(): HasMany
    {
        return $this->hasMany(LabRequest::class)->latest();
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest('invoice_date');
    }

    /**
     * Generate next sequential Medical Record Number.
     */
    public static function generateMRN(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('MRN-%s-%04d', $year, $count);
    }
}
