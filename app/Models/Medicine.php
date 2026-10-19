<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medicine extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'brand_name',
        'generic_name',
        'category',
        'dosage_form',
        'strength',
        'unit_price',
        'purchase_cost',
        'stock_quantity',
        'min_stock_alert',
        'batch_number',
        'expiry_date',
        'manufacturer',
        'storage_condition',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'purchase_cost' => 'decimal:2',
            'stock_quantity' => 'integer',
            'min_stock_alert' => 'integer',
            'expiry_date' => 'date',
        ];
    }

    public function prescriptionItems(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock_alert;
    }

    public function isOutOfStock(): bool
    {
        return $this->stock_quantity <= 0;
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public static function generateCode(): string
    {
        $last = static::where('code', 'like', 'MED-%')
            ->orderByRaw('LENGTH(code) DESC, code DESC')
            ->value('code');

        $nextSeq = 1;
        if ($last && preg_match('/MED-(\d+)/', $last, $matches)) {
            $nextSeq = ((int) $matches[1]) + 1;
        }

        return sprintf('MED-%04d', $nextSeq);
    }
}
