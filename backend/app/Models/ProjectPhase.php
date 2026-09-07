<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPhase extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'description',
        'completion_percentage',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'completion_percentage' => 'integer',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
