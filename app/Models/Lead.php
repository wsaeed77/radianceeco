<?php

namespace App\Models;

use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Enums\LeadStage;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lead extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        // Original fields
        'client_name',
        'client_dob',
        'client_number',
        'phone',
        'email',
        'house_number',
        'street_name',
        'city',
        'postcode',
        'address_line',
        'status_raw',
        'stage_raw',
        'status_notes_raw',
        'status',
        'stage',
        'grant',
        'job_categories',
        'possible_grant',
        'benefit',
        'poa',
        'epc',
        'gas_safe',
        'council_tax_band',
        'epr_report',
        'benefit_holder_name',
        'benefit_holder_dob',
        'agent',
        'agent_id',
        'dedupe_key',
        
        // New fields
        'first_name',
        'last_name',
        'address_line_1',
        'address_line_2',
        'assigned_to',
        'zip_code',
        'latitude',
        'longitude',
        'geocoded_at',
        'source',
        'source_details',
        'notes',
        'is_duplicate',
        'is_complete',
        
        // Eligibility Details
        'occupancy_type',
        'possible_grant_types',
        'benefit_type',
        'poa_info',
        'epc_rating',
        'epc_details',
        'gas_safe_info',
        'council_tax_band',
        'floor_area',
        'eligibility_client_dob',
        
        // Lead Status and Source additional fields
        'grant_type',
        
        // Data Match fields
        'data_match_status',
        'data_match_remarks',
        'multi_phone_numbers',
        
        // EPC fields
        'epc_data',
        'epc_fetched_at',
        'epc_recommendations',
        'epc_recommendations_fetched_at',
        
        // EPR fields
        'epr_measures',
        'epr_pre_rating',
        'epr_post_rating',
        'epr_abs',
        'epr_amount_funded',
        'epr_payments',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'client_dob' => 'date',
        'benefit_holder_dob' => 'date',
        'eligibility_client_dob' => 'date',
        'multi_phone_numbers' => 'json',
        'status' => LeadStatus::class,
        'stage' => LeadStage::class,
        'source' => LeadSource::class,
        'epc_data' => 'array',
        'epc_fetched_at' => 'datetime',
        'epc_recommendations' => 'array',
        'epc_recommendations_fetched_at' => 'datetime',
        'epr_measures' => 'array',
        'epr_payments' => 'array',
        'geocoded_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<string>
     */
    protected $appends = [
        'status_label',
        'stage_label',
    ];

    /**
     * Get the formatted status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return $this->status->label();
    }

    /**
     * Get the formatted stage label.
     */
    public function getStageLabelAttribute(): string
    {
        return $this->stage->label();
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        // Generate the dedupe key before saving
        static::creating(function ($lead) {
            $lead->dedupe_key = self::generateDedupeKey($lead);
            
            // Auto-combine address line if not provided
            if (empty($lead->address_line) && !empty($lead->house_number) && !empty($lead->street_name)) {
                $lead->address_line = trim("{$lead->house_number} {$lead->street_name}, {$lead->city}, {$lead->postcode}");
            }
            
            // Auto-set client_name from first_name and last_name
            if (empty($lead->client_name) && (!empty($lead->first_name) || !empty($lead->last_name))) {
                $lead->client_name = trim("{$lead->first_name} {$lead->last_name}");
            }
            
            // Auto-set postcode from zip_code
            if (empty($lead->postcode) && !empty($lead->zip_code)) {
                $lead->postcode = $lead->zip_code;
            }
            
            // Auto-set address_line from address_line_1
            if (empty($lead->address_line) && !empty($lead->address_line_1)) {
                $lead->address_line = $lead->address_line_1;
                if (!empty($lead->address_line_2)) {
                    $lead->address_line .= ', ' . $lead->address_line_2;
                }
                if (!empty($lead->city)) {
                    $lead->address_line .= ', ' . $lead->city;
                }
                if (!empty($lead->postcode)) {
                    $lead->address_line .= ', ' . $lead->postcode;
                }
            }
        });
        
        static::updating(function ($lead) {
            // Regenerate dedupe key if relevant fields changed
            if ($lead->isDirty(['client_name', 'first_name', 'last_name', 'house_number', 'street_name', 'postcode', 'zip_code'])) {
                $lead->dedupe_key = self::generateDedupeKey($lead);
            }
            
            // Update client_name if first/last name changed
            if ($lead->isDirty(['first_name', 'last_name'])) {
                $lead->client_name = trim("{$lead->first_name} {$lead->last_name}");
            }
            
            // Update postcode from zip_code
            if ($lead->isDirty(['zip_code']) && !empty($lead->zip_code)) {
                $lead->postcode = $lead->zip_code;
            }
            
            // Update address_line if components changed
            if ($lead->isDirty(['house_number', 'street_name', 'city', 'postcode'])) {
                $lead->address_line = trim("{$lead->house_number} {$lead->street_name}, {$lead->city}, {$lead->postcode}");
            }
            
            // Update address_line from address_line_1
            if ($lead->isDirty(['address_line_1', 'address_line_2', 'city', 'zip_code'])) {
                if (!empty($lead->address_line_1)) {
                    $lead->address_line = $lead->address_line_1;
                    if (!empty($lead->address_line_2)) {
                        $lead->address_line .= ', ' . $lead->address_line_2;
                    }
                    if (!empty($lead->city)) {
                        $lead->address_line .= ', ' . $lead->city;
                    }
                    if (!empty($lead->zip_code)) {
                        $lead->address_line .= ', ' . $lead->zip_code;
                    }
                }
            }
        });
    }

    /**
     * Generate a unique deduplication key based on name and address.
     */
    public static function generateDedupeKey($lead): string
    {
        $name = strtolower(trim($lead->client_name ?? ''));
        $postcode = strtolower(preg_replace('/\s+/', '', $lead->postcode ?? ''));
        $house = strtolower(preg_replace('/\s+/', '', $lead->house_number ?? ''));
        
        if (empty($name) || empty($postcode) || empty($house)) {
            return md5(uniqid(rand(), true)); // Fallback to random if missing components
        }
        
        return md5($postcode . $house . $name);
    }

    /**
     * Get the agent that owns the lead.
     */
    public function assignedAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get the activities for the lead.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Get the stage history records for the lead.
     */
    public function stageHistories(): HasMany
    {
        return $this->hasMany(StageHistory::class);
    }

    /**
     * Get the documents for the lead.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
    
    /**
     * Get the agent assigned to the lead.
     */
    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    /**
     * Get ECO4 calculations for this lead
     */
    public function eco4Calculations(): HasMany
    {
        return $this->hasMany(Eco4Calculation::class);
    }
}