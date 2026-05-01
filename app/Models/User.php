<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

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
        'password' => 'hashed',
    ];

    // Relasi
    public function projects()
    {
        return $this->hasMany(Project::class, 'pic_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'submitted_by');
    }

    // Helper role
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isProduser(): bool
    {
        return $this->role === 'produser';
    }

    public function isCrew(): bool
    {
        return $this->role === 'crew';
    }
    public function assignedProjects()
    {
    return $this->hasMany(Project::class, 'pic_id');
    }

    public function crewProfile()
    {
    return $this->hasOne(Crew::class);
    }
}
