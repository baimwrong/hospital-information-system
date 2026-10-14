<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabTestResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_request_id',
        'lab_test_type_id',
        'parameter_name',
        'result_value',
        'unit',
        'reference_range',
        'flag',
        'comments',
    ];

    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    public function labTestType(): BelongsTo
    {
        return $this->belongsTo(LabTestType::class);
    }
}
