<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }

    public function stockAdjustments(): HasMany
    {
        return $this->hasMany(StockAdjustment::class);
    }

    public function cancelledSales(): HasMany
    {
        return $this->hasMany(Sale::class, 'cancelled_by');
    }

    public function isGerant(): bool
    {
        return $this->role === 'gerant';
    }

    public function isVendeur(): bool
    {
        return $this->role === 'vendeur';
    }
}
