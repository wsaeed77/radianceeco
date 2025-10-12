<?php

namespace App\Models;

use App\Enums\DocumentKind;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory, HasUuid;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'lead_id',
        'activity_id',
        'kind',
        'name',
        'disk',
        'path',
        'size_bytes',
        'uploaded_by',
        'uploaded_at',
    ];
    
    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'kind' => DocumentKind::class,
        'uploaded_at' => 'datetime',
    ];
    
    /**
     * Get the lead that owns the document.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
    
    /**
     * Get the activity that the document is attached to.
     */
    public function activity(): BelongsTo
    {
        return $this->belongsTo(Activity::class);
    }
    
    /**
     * Get the user that uploaded the document.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}