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
    public function students()
    {
        return $this->hasMany(Student::class);
    }
}
