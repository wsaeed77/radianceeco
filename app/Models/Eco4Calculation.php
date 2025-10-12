<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Eco4Calculation extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'lead_id',
        'scheme',
        'calculation_type',
        'starting_sap_score',
        'starting_sap_band',
        'finishing_sap_score',
        'finishing_sap_band',
        'floor_area_band',
        'property_type',
        'wall_type',
        'country',
        'pre_main_heat_source',
        'post_main_heat_source',
        'pps_eco_rate',
        'innovation_multiplier',
        'total_abs',
        'total_eco_value',
        'summary',
    ];

    protected $casts = [
        'starting_sap_score' => 'integer',
        'finishing_sap_score' => 'integer',
        'pps_eco_rate' => 'decimal:2',
        'innovation_multiplier' => 'decimal:2',
        'total_abs' => 'decimal:2',
        'total_eco_value' => 'decimal:2',
        'summary' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function measures(): HasMany
    {
        return $this->hasMany(Eco4Measure::class, 'calculation_id');
    }
}
