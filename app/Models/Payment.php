<?php

namespace App\Models;

use App\Services\SequenceGenerator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'payment_number',
        'invoice_id',
        'patient_id',
        'amount',
        'payment_method',
        'transaction_reference',
        'notes',
        'recorded_by_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by_id');
    }

    /**
     * Generate sequential Payment receipt number atomically and collision-free.
     */
    public static function generateNumber(): string
    {
        return SequenceGenerator::generate('PAY', 'payments', 'payment_number', 4, true);
    }
}
