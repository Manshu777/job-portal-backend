<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateExperience extends Model
{
    use HasFactory;
protected $table = 'candidate_experiences';
    protected $fillable = [
        'candidate_id',
        'job_title',
        'job_roles',
        'company_name',
        'experience_years',
        'experience_months',
        'current_salary',
        'start_date',
    ];

    protected $casts = [
        'job_roles' => 'array',
    ];

    public function candidate()
    {
        return $this->belongsTo(Candidate::class);
    }
}
