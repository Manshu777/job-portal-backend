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

    /**
     * Set the query for the export (passed from Filament table).
     *
     * @param Builder $query
     */
    public function __construct(Builder $query)
    {
        $this->query = $query;
    }

    /**
     * Return the query to fetch candidate data.
     *
     * @return Builder
     */
    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * Define the headers for the Excel file.
     *
     * @return array
     */
    public function headings(): array
    {
        return [
            'full_name',
            'email',
            'dob',
            'gender',
            'address',
            'city',
            'state',
            'prefers_night_shift',
            'prefers_day_shift',
            'work_from_home',
            'work_from_office',
            'field_job',
            'employment_type',
            'resume',
            'skills',
            'active_user',
            'doneprofile',
            'total_jobs_applied',
            'total_job_views',
            'last_login',
        ];
    }

    /**
     * Map each candidate row to the desired format.
     *
     * @param Candidate $candidate
     * @return array
     */
    public function map($candidate): array
    {
        return [
            $candidate->full_name,
            $candidate->email,
            $candidate->dob ? $candidate->dob->format('Y-m-d') : null,
            $candidate->gender,
            $candidate->address,
            $candidate->city,
            $candidate->state,
            $candidate->prefers_night_shift ? 1 : 0,
            $candidate->prefers_day_shift ? 1 : 0,
            $candidate->work_from_home ? 1 : 0,
            $candidate->work_from_office ? 1 : 0,
            $candidate->field_job ? 1 : 0,
            $candidate->employment_type,
            $candidate->resume,
            is_array($candidate->skills) ? implode(', ', $candidate->skills) : $candidate->skills,
            $candidate->active_user ? 1 : 0,
            $candidate->doneprofile ? 1 : 0,
            $candidate->total_jobs_applied,
            $candidate->total_job_views,
            $candidate->last_login ? $candidate->last_login->format('Y-m-d H:i:s') : null,
        ];
    }
}