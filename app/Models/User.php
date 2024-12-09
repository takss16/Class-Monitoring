<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    public function isTeacher()
    {
        return $this->user_type === 'teacher';
    }
    public function subjects()
        {
            return $this->belongsToMany(Subject::class, 'teacher_subject_section')
                        ->withPivot('section_id')
                        ->withTimestamps();
        }

        public function sections()
        {
            return $this->belongsToMany(Section::class, 'teacher_subject_section')
                        ->withPivot('subject_id')
                        ->withTimestamps();
        }

}
