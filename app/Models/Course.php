<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'subject_id', 'teacher_id', 'department_id', 'year', 'semester'];

    /**
     * A course belongs to a subject.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * A course belongs to a department.
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * A course has many book requests.
     */
    public function bookRequests()
    {
        return $this->hasMany(BookRequest::class);
    }
}
