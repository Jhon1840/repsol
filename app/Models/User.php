<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';

    public const ROLE_MARKETING = 'marketing';

    public const ROLE_BRANCH_MANAGER = 'branch_manager';

    public const RIDER_BRANCH_SCOPED_ROLES = [
        self::ROLE_MARKETING,
        self::ROLE_BRANCH_MANAGER,
    ];

    public const BRANCH_OPTIONS = [
        'COCHABAMBA' => 'COCHABAMBA',
        'LA PAZ' => 'LA PAZ',
        'SANTA CRUZ' => 'SANTA CRUZ',
        'SUCRE' => 'SUCRE',
        'TARIJA' => 'TARIJA',
    ];

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
        'branch',
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
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($this->hasRiderBranchScopedRole()) {
            return filled($this->branch);
        }

        return $this->isAdmin();
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isBranchManager(): bool
    {
        return $this->role === self::ROLE_BRANCH_MANAGER;
    }

    public function hasRiderBranchScopedRole(): bool
    {
        return in_array($this->role, self::RIDER_BRANCH_SCOPED_ROLES, true);
    }

    public function branchScope(): ?string
    {
        return $this->hasRiderBranchScopedRole() && filled($this->branch)
            ? $this->branch
            : null;
    }
}
