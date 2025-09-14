<?php

namespace App\Exports;

use App\Models\Candidate;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Database\Eloquent\Builder;

class CandidateTemplateExport implements FromQuery, WithHeadings, WithMapping
{
    protected $query;

    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
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
            'preferred_job_titles', // JSON array or comma-separated
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
            'skills', // JSON array or comma-separated
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
            'password', // Plain text for import
            'doneprofile',
            'start_date',
            'end_date',
            'english_level',
            'preferred_locations', // JSON array or comma-separated
            'preferred_languages', // JSON array or comma-separated
            'profile_pic',
        ];
    }

    public function map($candidate): array
    {
        return [
            $candidate->full_name,
            $candidate->dob ? $candidate->dob->format('Y-m-d') : null,
            $candidate->gender,
            $candidate->email,
            $candidate->address,
            $candidate->city,
            $candidate->state,
            $candidate->degree,
            $candidate->specialization,
            $candidate->college_name,
            $candidate->education_level,
            $candidate->currently_pursuing ? 1 : 0,
            $candidate->highest_education,
            $candidate->complete_years,
            $candidate->complete_month,
            $candidate->school_medium,
            $candidate->passing_marks,
            $candidate->pursuing ? 1 : 0,
            $candidate->experience_years,
            $candidate->experience_months,
            $candidate->experience_level,
            $candidate->is_working ? 1 : 0,
            $candidate->notice_period,
            $candidate->job_title,
            $candidate->job_roles,
            is_array($candidate->preferred_job_titles) ? implode(', ', $candidate->preferred_job_titles) : $candidate->preferred_job_titles,
            $candidate->company_name,
            $candidate->current_salary,
            $candidate->prefers_night_shift ? 1 : 0,
            $candidate->prefers_day_shift ? 1 : 0,
            $candidate->work_from_home ? 1 : 0,
            $candidate->work_from_office ? 1 : 0,
            $candidate->field_job ? 1 : 0,
            $candidate->experience_type,
            $candidate->employment_type,
            $candidate->preferred_language,
            $candidate->resume,
            is_array($candidate->skills) ? implode(', ', $candidate->skills) : $candidate->skills,
            $candidate->active_user ? 1 : 0,
            $candidate->last_login ? $candidate->last_login->format('Y-m-d H:i:s') : null,
            $candidate->total_jobs_applied,
            $candidate->total_job_views,
            $candidate->created_at ? $candidate->created_at->format('Y-m-d H:i:s') : null,
            $candidate->updated_at ? $candidate->updated_at->format('Y-m-d H:i:s') : null,
            $candidate->otp,
            $candidate->otp_expires_at ? $candidate->otp_expires_at->format('Y-m-d H:i:s') : null,
            $candidate->number,
            $candidate->token,
            $candidate->password ? '***HASHED***' : null, // Don't export actual passwords
            $candidate->doneprofile ? 1 : 0,
            $candidate->start_date ? $candidate->start_date->format('Y-m-d') : null,
            $candidate->end_date ? $candidate->end_date->format('Y-m-d') : null,
            $candidate->english_level,
            is_array($candidate->preferred_locations) ? implode(', ', $candidate->preferred_locations) : $candidate->preferred_locations,
            is_array($candidate->preferred_languages) ? implode(', ', $candidate->preferred_languages) : $candidate->preferred_languages,
            $candidate->profile_pic,
        ];
    }
}