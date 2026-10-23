<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image_path',
        'department_id',
        'target_amount',
        'current_amount',
        'status',
        'latitude',
        'longitude',
        'created_by',
    ];

    protected $appends = [
        'image_url',
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

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        if (str_starts_with($this->image_path, 'http://') || str_starts_with($this->image_path, 'https://')) {
            return $this->image_path;
        }

        $disk = config('balagh.storage.disk', 'public');
        return Storage::disk($disk)->url($this->image_path);
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
        return $this->hasMany(ProjectPhase::class)->orderBy('start_date', 'asc')->orderBy('id', 'asc');
    }

    public function contributions()
    {
        return $this->hasMany(ProjectContribution::class);
    }
}
