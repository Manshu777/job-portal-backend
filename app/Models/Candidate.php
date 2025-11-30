<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class Candidate extends Model
{
    use HasFactory, HasApiTokens;

    protected $table = 'candidates';

    protected $fillable = [
        'full_name',
        'dob',
        'gender',
        'email',
        'address',
        'city',
        'state',

        'immediate_joiner',
    'open_to_opportunities',

        // Education (only highest level & status)
        'currently_pursuing',
        'highest_education',

        // Experience (summary)
        'experience_years',
        'experience_months',
        'experience_level',
        'is_working',
        'notice_period',

        // Current Job (summary)
        'job_title',
        'company_name',
        'current_salary',

        // Preferences
        'prefers_night_shift',
        'prefers_day_shift',
        'work_from_home',
        'work_from_office',
        'preferred_language',
        'english_level',

        // Arrays (JSON)
        'skills',
        'preferred_job_titles',
        'preferred_languages',
        'preferred_locations',

        // Files & Auth
        'resume',
        'profile_pic',
        'password',
        'number',
        'token',
        'otp',
        'otp_expires_at',

        // Meta
        'active_user',
        'last_login',
        'total_jobs_applied',
        'total_job_views',
        'doneprofile',
    ];

    protected $casts = [
        'skills'               => 'array',
        'preferred_job_titles' => 'array',
        'preferred_languages'  => 'array',
        'preferred_locations'  => 'array',
        'dob'                  => 'date',
        'doneprofile'          => 'boolean',
    ];

    protected $hidden = [
        'password',
        'token',
        'otp',
        'otp_expires_at',
    ];

    // =================================================================
    // RELATIONSHIPS
    // =================================================================

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

    public function employerview()
    {
        return $this->belongsToMany(Employer::class, 'employer_candidate_views')
                    ->withPivot('number_revealed', 'revealed_at')
                    ->withTimestamps();
    }

    // =================================================================
    // SCOPES
    // =================================================================

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['city'])) {
            $query->whereRaw('LOWER(city) LIKE ?', ['%' . strtolower($filters['city']) . '%']);
        }

        if (!empty($filters['min_experience'])) {
            $query->whereRaw('(experience_years * 12 + experience_months) >= ?', [(int)$filters['min_experience'] * 12]);
        }

        if (!empty($filters['max_experience'])) {
            $query->whereRaw('(experience_years * 12 + experience_months) <= ?', [(int)$filters['max_experience'] * 12]);
        }

        // Filter by Graduation Degree
        if (!empty($filters['graduation_degree'])) {
            $query->whereHas('educations', function ($q) use ($filters) {
                $q->where('education_type', 'graduation')
                  ->whereRaw('LOWER(education_level) LIKE ?', ['%' . strtolower($filters['graduation_degree']) . '%']);
            });
        }

        // Filter by Post-Graduation Degree
        if (!empty($filters['post_graduation_degree'])) {
            $query->whereHas('educations', function ($q) use ($filters) {
                $q->where('education_type', 'post_graduation')
                  ->whereRaw('LOWER(education_level) LIKE ?', ['%' . strtolower($filters['post_graduation_degree']) . '%']);
            });
        }

        // Filter by Specialization (any education)
        if (!empty($filters['specialization'])) {
            $query->whereHas('educations', function ($q) use ($filters) {
                $q->whereRaw('LOWER(specialization) LIKE ?', ['%' . strtolower($filters['specialization']) . '%']);
            });
        }
    }

    // =================================================================
    // ACCESSORS / MUTATORS (Optional)
    // =================================================================

    public function getProfilePicUrlAttribute()
    {
        return $this->profile_pic ? asset('storage/' . $this->profile_pic) : null;
    }

    public function getResumeUrlAttribute()
    {
        return $this->resume ? asset('storage/' . $this->resume) : null;
    }
    public function graduation()
{
    return $this->educations()->where('education_type', 'graduation')->first();
}

public function postGraduation()
{
    return $this->educations()->where('education_type', 'post_graduation')->first();
}
}