<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'employer_id',
        'name',
        
        'gst_certificate',
        'other_certificate',
        'is_approved',
        'company_location',
        'contact_person',
        'contact_phone',
        'gst_number',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
    ];

    public function employer()
    {
        return $this->belongsTo(Employer::class);
    }

    public function jobPostings()
    {
        return $this->hasMany(JobPosting::class);
    }
}