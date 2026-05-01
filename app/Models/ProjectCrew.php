<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectCrew extends Model
{
    use HasFactory;

    protected $table = 'project_crews';

    protected $fillable = [
        'project_id',
        'crew_id',
        'gaji_per_hari',
        'total_hari',
        'total_gaji',
        'status',
    ];

    protected $casts = [
        'gaji_per_hari' => 'decimal:2',
        'total_gaji' => 'decimal:2',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function crew()
    {
        return $this->belongsTo(Crew::class);
    }
}
