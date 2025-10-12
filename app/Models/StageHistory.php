<?php

namespace App\Models;

use App\Enums\LeadStage;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StageHistory extends Model
{
    use HasFactory, HasUuid;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'lead_id',
        'from_stage',
        'to_stage',
        'note',
        'changed_by',
        'changed_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'from_stage' => LeadStage::class,
        'to_stage' => LeadStage::class,
        'changed_at' => 'datetime',
    ];
    
    /**
     * Get the lead that owns the stage history.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
    
    /**
     * Get the user that changed the stage.
     */
    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
    
    /**
     * Alias for changedBy to maintain consistency with Lead::with(['stageHistories.user'])
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}