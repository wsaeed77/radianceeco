<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => Role::class,
    ];
    /**
     * Get the leads assigned to this user.
     */
    public function assignedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'agent_id');
    }
    
    /**
     * Alias for assignedLeads for convenience in queries
     */
    public function leads(): HasMany
    {
        return $this->assignedLeads();
    }
    
    /**
     * Check if the user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }
    
    /**
     * Check if the user is a manager.
     */
    public function isManager(): bool
    {
        return $this->role === Role::MANAGER;
    }
    
    /**
     * Check if the user is an agent.
     */
    public function isAgent(): bool
    {
        return $this->role === Role::AGENT;
    }
    
    /**
     * Check if the user is readonly.
     */
    public function isReadOnly(): bool
    {
        return $this->role === Role::READONLY;
    }
}
