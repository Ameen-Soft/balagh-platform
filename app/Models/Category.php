<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'parent_id',
        'department_id',
        'name',
        'description',
        'level',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
        ];
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

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'category_id');
    }
}
