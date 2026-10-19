<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Admission extends Model
{
    use HasFactory;

    protected $fillable = [
        'admission_number',
        'patient_id',
        'doctor_id',
        'bed_id',
        'admission_date',
        'discharge_date',
        'admission_reason',
        'status',
        'discharge_summary',
        'condition_at_discharge',
    ];

    protected function casts(): array
    {
        return [
            'admission_date' => 'datetime',
            'discharge_date' => 'datetime',
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

    public function bed(): BelongsTo
    {
        return $this->belongsTo(Bed::class);
    }

    public function invoice(): HasOne
    {
        return $this->hasOne(Invoice::class);
    }

    public function getDurationDaysAttribute(): int
    {
        $end = $this->discharge_date ? Carbon::parse($this->discharge_date) : now();
        $start = Carbon::parse($this->admission_date);

        return max(1, (int) ceil($start->diffInHours($end) / 24));
    }

    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "ADM-{$year}-";

        $last = static::where('admission_number', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(admission_number) DESC, admission_number DESC')
            ->value('admission_number');

        $nextSeq = 1;
        if ($last && preg_match('/ADM-\d{4}-(\d+)/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return sprintf('ADM-%s-%04d', $year, $nextSeq);
    }
}
