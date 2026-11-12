<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'is_active',
    ];

    public function penghuni()
    {
        return $this->hasOne(Penghuni::class);
    }

    public function isWarga(): bool
    {
        return $this->role === 'warga';
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin' || $this->role === 'admin' || empty($this->role);
    }

    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isAdmin(): bool
    {
        return $this->isSuperAdmin() || $this->isOperator();
    }

    public function peranLabel(): string
    {
        if ($this->isWarga()) {
            return 'Warga Kavling';
        }
        if ($this->isOperator()) {
            return 'Operator';
        }
        if ($this->isSuperAdmin()) {
            return 'Superadmin';
        }

        return 'Pengelola';
    }

    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

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
}
