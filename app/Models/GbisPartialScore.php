<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GbisPartialScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'measure_category',
        'measure_type',
        'pre_main_heating_source',
        'floor_area_band',
        'starting_band',
        'average_treatable_factor',
        'cost_savings',
    ];

    protected $casts = [
        'average_treatable_factor' => 'decimal:4',
        'cost_savings' => 'decimal:2',
    ];

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
