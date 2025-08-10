<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'college_id'];

    /**
     * A department belongs to a college.
     */
    public function college()
    {
        return $this->belongsTo(College::class);
    }

    /**
     * A department has many subjects.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'department_subject');
    }

    /**
     * A department has many courses.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
