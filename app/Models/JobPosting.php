<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Parallax\FilamentComments\Models\Traits\HasFilamentComments;
use Illuminate\Support\Str; // Ensure this import is present
class JobPosting extends Model
{
    use HasFactory,HasFilamentComments;

    protected $fillable = [
        'employer_id',
        
        'company_id', // Add company_id
        'job_title',
        'job_type',
        'location',
        'work_location_type',
        'compensation',
        'pay_type',
        'joining_fee',
        'basic_requirements',
        'additional_requirements',
        'job_description',
        'is_walkin_interview',
        'communication_preference',
        'total_experience_required',
        'other_job_titles',
        'degree_specialization',
        'job_expire_time',
        'number_of_candidates_required',
        'is_verified',
        'english_level', // Add for englishLevel
    'gender_preference', // Add for genderPreference
    'perks', // Add for perks (as JSON)
    'interview_location', // Add for interviewLocation
    'contact_email', // Add for contactEmail
    'contact_phone', // Add for contactPhone
    'interview_date', // Add for interviewDate
    'interview_time', // Add for interviewTime
    'not_email', // Add for notEmail
    'industry', // Added
        'department', // Added
        'job_role', // Added
    'viewed_number', // Add for viewedNumber
     'min_salary',
        'max_salary',
        'incentive',
        'slug'
        
    ];

   protected $casts = [
        'additional_requirements' => 'array',
        'other_job_titles' => 'array',
        'perks' => 'array', // Add for perks
        'degree_specialization' => 'array',
        'joining_fee' => 'boolean',
        'is_walkin_interview' => 'boolean',
    ];


    protected static function boot()
    {
        parent::boot();

        // Automatically generate slug before saving
        static::saving(function ($model) {
            $model->slug = $model->generateSlug();
        });
    }

    // Method to generate SEO-friendly slug
    public function generateSlug()
    {
        $baseSlug = Str::slug($this->job_title . '-' . $this->location);

        // Ensure uniqueness by appending a number suffixes if the slug already exists
        $slug = $baseSlug;
        $count = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $baseSlug . '-' . $count++;
        }

        return $slug;
    }


    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function applications()
    {
        return $this->hasMany(JobPostingApplication::class);
    }

    // Method to get active applications count
    public function activeApplicationsCount()
    {
        return $this->applications()->where('status', 'active')->count();
    }

    // Method to get inactive applications count
    public function inactiveApplicationsCount()
    {
        return $this->applications()->where('status', 'inactive')->count();
    }
    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
