<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ministry extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'logo',
        'contact_email',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    public function complaints()
    {
        return $this->hasManyThrough(
            Complaint::class,
            Department::class,
            'ministry_id',            // Foreign key on departments table...
            'current_department_id',  // Foreign key on complaints table...
            'id',                     // Local key on ministries table...
            'id'                      // Local key on departments table...
        );
    }
}
