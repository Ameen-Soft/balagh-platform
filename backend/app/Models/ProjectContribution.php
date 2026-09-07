<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectContribution extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'project_id',
        'contributor_id',
        'amount',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
        ];
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function contributor()
    {
        return $this->belongsTo(User::class, 'contributor_id');
    }
}
