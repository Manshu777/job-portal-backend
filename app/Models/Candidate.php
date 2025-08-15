<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\CandidateEducation;
use App\Models\CandidateExperience;
use App\Models\CandidateSkill;
use App\Models\CandidateLanguage;


use Laravel\Sanctum\HasApiTokens;
class Candidate extends Model
{

       use HasFactory,HasApiTokens;
    protected $table = 'candidates';

    protected $fillable = [
 'full_name',
        'dob',
        'gender',
        'email',
        'address',
        'city',
        'state',
        'degree',
        'specialization',
        'college_name',
        'education_level',
        'currently_pursuing',
        'highest_education',
        'complete_years',
        'complete_month',
        'school_medium',
        'passing_marks',
        'pursuing',
        'experience_years',
        'experience_months',
        'experience_level',
        'is_working',
        'notice_period',
        'job_title',
        'job_roles',
        'preferred_job_titles',
        'company_name',
        'current_salary',
        'prefers_night_shift',
        'prefers_day_shift',
        'work_from_home',
        'work_from_office',
        'field_job',
        'experience_type',
        'employment_type',
        'preferred_language',
        'resume',
        'skills',
        'active_user',
        'last_login',
        'total_jobs_applied',
        'total_job_views',
        'created_at',
        'updated_at',
        'otp',
        'otp_expires_at',
        'number',
        'token',
        'password',
        'doneprofile',
        'start_date',
        'end_date',
        'english_level',
    ];

  protected $casts = [
    'skills' => 'array',
];

    public function educations()
    {
        return $this->hasMany(CandidateEducation::class);
    }

    public function experiences()
    {
        return $this->hasMany(CandidateExperience::class);
    }

    public function skills()
    {
        return $this->hasMany(CandidateSkill::class);
    }

    public function languages()
    {
        return $this->hasMany(CandidateLanguage::class);
    }
    public function employerViews()
    {
        return $this->belongsToMany(Employer::class, 'employer_candidate_views')
                    ->withPivot('number_revealed', 'revealed_at')
                    ->withTimestamps();
    }
}
