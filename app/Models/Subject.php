<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'academic_id'];

    /**
     * A subject belongs to an academic.
     */
    public function academic()
    {
        return $this->belongsTo(User::class, 'academic_id');
    }

    /**
     * A subject belongs to many departments.
     */
    public function departments()
    {
        return $this->belongsToMany(Department::class, 'department_subject');
    }

    /**
     * A subject has many courses.
     */
    public function courses()
    {
        return $this->hasMany(Course::class);
    }
}
