<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use Notifiable;

    protected $fillable = [
        'name',
        'role',
        'email',
        'phone',
        'photo_url',
        'address',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function createdPoles(): HasMany
    {
        return $this->hasMany(Pole::class, 'created_by_id');
    }

    public function updatedPoles(): HasMany
    {
        return $this->hasMany(Pole::class, 'updated_by_id');
    }
}
