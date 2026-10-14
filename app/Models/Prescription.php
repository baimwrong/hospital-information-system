<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'prescription_number',
        'patient_id',
        'doctor_id',
        'medical_record_id',
        'status',
        'dispensed_by_id',
        'dispensed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dispensed_at' => 'datetime',
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

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function getTotalCostAttribute(): float
    {
        return (float) $this->items->sum('subtotal');
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;

        return sprintf('RX-%s-%04d', $year, $count);
    }
}
