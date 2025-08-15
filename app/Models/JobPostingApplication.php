<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPostingApplication extends Model
{
    protected $fillable = [
        'candidate_id',
        'job_posting_id',
        'status',
    ];

    public function jobPosting()
    {
        return $this->belongsTo(JobPosting::class, 'job_posting_id');
    }

    public function candidate()
    {
        return $this->belongsTo(Candidate::class, 'candidate_id');
    }
}
