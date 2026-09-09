<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'department_id',
        'target_amount',
        'current_amount',
        'status',
        'latitude',
        'longitude',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'target_amount' => 'decimal:2',
            'current_amount' => 'decimal:2',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function creator()
    {
        return $this->createdBy();
    }

    public function getNameAttribute(): string
    {
        return $this->title ?? '';
    }

    public function getBudgetAttribute(): float
    {
        return (float) ($this->target_amount ?? 0);
    }

    public function getProgressPercentageAttribute(): int
    {
        if ($this->target_amount > 0) {
            return (int) min(100, round((($this->current_amount ?? 0) / $this->target_amount) * 100));
        }
        return 0;
    }

    public function getBeneficiariesCountAttribute(): int
    {
        return 2500;
    }

    public function phases()
    {
        return $this->hasMany(ProjectPhase::class);
    }

    public function contributions()
    {
        return $this->hasMany(ProjectContribution::class);
    }
}
