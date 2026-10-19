<?php

namespace App\Models;

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
     * Generate sequential Payment receipt number atomically.
     */
    public static function generateNumber(): string
    {
        $year = date('Y');
        $prefix = "PAY-{$year}-";

        $last = static::withTrashed()
            ->where('payment_number', 'like', "{$prefix}%")
            ->orderByRaw('LENGTH(payment_number) DESC, payment_number DESC')
            ->value('payment_number');

        $nextSeq = 1;
        if ($last && preg_match('/PAY-\d{4}-(\d+)/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return sprintf('PAY-%s-%04d', $year, $nextSeq);
    }
}
