<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eco4PartialScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'measure_category',
        'measure_type',
        'pre_main_heating_source',
        'post_main_heating_source',
        'floor_area_band',
        'starting_band',
        'average_treatable_factor',
        'cost_savings',
    ];

    protected $casts = [
        'average_treatable_factor' => 'decimal:4',
        'cost_savings' => 'decimal:2',
    ];

    /**
     * Find matching scores based on criteria
     */
    public static function findMatching(array $criteria)
    {
        $query = self::query();

        foreach ($criteria as $key => $value) {
            if ($value !== null && $value !== '') {
                $query->where($key, $value);
            }
        }

        return $query->get();
    }
}
