<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'ministry_id',
        'parent_id',
        'department_id',
        'name',
        'description',
        'level',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            // Level is strictly derived from parent_id presence
            $category->level = empty($category->parent_id) ? 1 : 2;

            if ($category->description === null) {
                $category->description = '';
            }

            // Auto-populate ministry_id if missing from department or parent
            if (empty($category->ministry_id)) {
                if ($category->department_id && $category->department) {
                    $category->ministry_id = $category->department->ministry_id;
                } elseif ($category->parent_id && $category->parent) {
                    $category->ministry_id = $category->parent->ministry_id;
                }
            }
        });
    }

    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function activeChildren()
    {
        return $this->hasMany(Category::class, 'parent_id')->where('is_active', true);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'category_id');
    }

    /**
     * Scope to active categories whose parents (if any) are also active.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('parent_id')
                    ->orWhereHas('parent', function ($pq) {
                        $pq->where('is_active', true);
                    });
            });
    }

    /**
     * Aggregate total complaints count (direct complaints on this category + all child complaints).
     */
    public function getTotalComplaintsCountAttribute(): int
    {
        if (isset($this->attributes['total_complaints_count'])) {
            return (int) $this->attributes['total_complaints_count'];
        }

        if ($this->level === 1) {
            $childIds = $this->children()->pluck('id')->push($this->id);
            return Complaint::whereIn('category_id', $childIds)->count();
        }

        return $this->complaints()->count();
    }
}
