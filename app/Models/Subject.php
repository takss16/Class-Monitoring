<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subject extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_code',
        'name',
        'description',
        'user_id',
    ];

    public function section()
    {
        return $this->belongsTo(Section::class);
    }
    public function teachers()
    {
        return $this->belongsToMany(User::class, 'teacher_subject_section', 'subject_id', 'teacher_id')
                    ->withPivot('section_id')
                    ->withTimestamps();
    }
    
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_enrollments')
                    ->withPivot('section_id')
                    ->withTimestamps();
    }
    
}
