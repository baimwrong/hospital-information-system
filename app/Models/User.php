<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'avatar',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Doctor profile relation if user is a doctor.
     */
    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class);
    }

    /**
     * Audit logs authored by this user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Role checks.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isDoctor(): bool
    {
        return $this->role === 'doctor';
    }

    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    public function isPharmacist(): bool
    {
        return $this->role === 'pharmacist';
    }

    public function isLaboratorist(): bool
    {
        return $this->role === 'laboratorist';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }

    /**
     * Check if user has any of the specified roles.
     *
     * @param  string|list<string>  $roles
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_string($roles)) {
            $roles = [$roles];
        }

        return in_array($this->role, $roles, true);
    }
}
