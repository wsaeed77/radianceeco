<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Eco4Measure extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'calculation_id',
        'measure_type',
        'measure_variant',
        'measure_category',
        'post_heat_source',
        'percentage_treated',
        'is_innovation_measure',
        'abs_value',
        'pps_points',
        'eco_value',
        'matrix_data',
    ];

    protected $casts = [
        'percentage_treated' => 'integer',
        'is_innovation_measure' => 'boolean',
        'abs_value' => 'decimal:2',
        'pps_points' => 'decimal:2',
        'eco_value' => 'decimal:2',
        'matrix_data' => 'array',
    ];

    public function calculation(): BelongsTo
    {
        return $this->belongsTo(Eco4Calculation::class, 'calculation_id');
    }
}
