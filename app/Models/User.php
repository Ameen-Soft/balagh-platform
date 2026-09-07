<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Jetstream\HasTeams;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasProfilePhoto;
    use HasTeams;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'national_id',
        'department_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
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

    /*
    |--------------------------------------------------------------------------
    | Balagh Platform Relationships
    |--------------------------------------------------------------------------
    */

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'citizen_id');
    }

    public function assignedFieldWork()
    {
        return $this->hasMany(FieldAssignment::class, 'worker_id');
    }

    public function dispatchedFieldWork()
    {
        return $this->hasMany(FieldAssignment::class, 'assigned_by');
    }

    public function createdProjects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function projectContributions()
    {
        return $this->hasMany(ProjectContribution::class, 'contributor_id');
    }

    public function transferredComplaints()
    {
        return $this->hasMany(ComplaintTransfer::class, 'transferred_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class, 'user_id');
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'user_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Role-Based Access Control (RBAC) Helpers
    |--------------------------------------------------------------------------
    */

    public function hasRole(string|array $roles): bool
    {
        $roles = (array) $roles;
        if ($this->relationLoaded('roles')) {
            return $this->roles->pluck('name')->intersect($roles)->isNotEmpty();
        }
        return $this->roles()->whereIn('name', $roles)->exists();
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->roles->flatMap->permissions->pluck('name')->contains($permission);
        }
        return $this->roles()->whereHas('permissions', function ($query) use ($permission) {
            $query->where('name', $permission);
        })->exists();
    }
}
