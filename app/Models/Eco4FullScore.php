<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Eco4FullScore extends Model
{
    use HasFactory;

    protected $fillable = [
        'floor_area_band',
        'starting_band',
        'finishing_band',
        'cost_savings',
    ];

    protected $casts = [
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

        return $query->first();
    }
}
