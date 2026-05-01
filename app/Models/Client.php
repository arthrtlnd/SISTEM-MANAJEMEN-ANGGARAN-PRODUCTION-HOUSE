<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_klien','industri','kontak_person','email','telepon','tipe',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
