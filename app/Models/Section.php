<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'user_id',
    ];

    // public function students()
    // {
    //     return $this->hasMany(Student::class);
    // }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teachers()
{
    return $this->belongsToMany(User::class, 'teacher_subject_section', 'section_id', 'teacher_id')
                ->withPivot('subject_id')
                ->withTimestamps();
}

public function students()
{
    return $this->belongsToMany(Student::class, 'student_enrollments')
                ->withPivot('subject_id')
                ->withTimestamps();
}

}
