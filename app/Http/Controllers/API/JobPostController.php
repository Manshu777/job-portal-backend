<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobPosting; 
use App\Models\Candidate; 


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use App\Models\Company;
use App\Models\JobPostingApplication;
use App\Mail\JobPostingMail;
use App\Mail\NewCompanyRegistered;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
class JobPostController extends Controller
{
    
public function indexForEmployer($employerId)
{
    \Log::info('Entering indexForEmployer', [
        'employer_id' => $employerId
    ]);

    try {
        // Fetch jobs belonging to the employer
        \Log::debug('Fetching jobs for employer', [
            'employer_id' => $employerId
        ]);
        $jobs = JobPosting::where('employer_id', $employerId)->get();
        \Log::info('Jobs retrieved', [
            'employer_id' => $employerId,
            'job_count' => $jobs->count()
        ]);

        // For each job, compute matches count and applications count
        foreach ($jobs as $job) {
            \Log::debug('Processing job for matches and applications', [
                'job_id' => $job->id,
                'job_title' => $job->job_title,
                'total_experience_required' => $job->total_experience_required ?? 'Not specified'
            ]);

            $matchesQuery = $this->getMatchingCandidates($job);
            $job->matches = $matchesQuery->count();
            \Log::debug('Computed matches for job', [
                'job_id' => $job->id,
                'match_count' => $job->matches,
                'job_title' => $job->job_title,
                'total_experience_required' => $job->total_experience_required ?? 'Not specified'
            ]);

            $job->applications = JobPostingApplication::where('job_posting_id', $job->id)->count();
            \Log::debug('Computed applications for job', [
                'job_id' => $job->id,
                'application_count' => $job->applications
            ]);
        }

        \Log::info('Successfully processed jobs for employer', [
            'employer_id' => $employerId,
            'total_jobs' => $jobs->count()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $jobs,
        ], 200);
    } catch (\Exception $e) {
        \Log::error('Failed to process indexForEmployer', [
            'employer_id' => $employerId,
            'error' => $e->getMessage()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch jobs: ' . $e->getMessage()
        ], 500);
    }
}


private function getMatchingCandidates(JobPosting $job)
{
    \Log::info('Entering getMatchingCandidates', [
        'job_id' => $job->id,
        'job_title' => $job->job_title
    ]);

    $query = Candidate::query();
    \Log::debug('Initialized candidate query', [
        'job_id' => $job->id
    ]);

    // Prepare filters for scopeFilter
    $filters = [];
    \Log::debug('Preparing filters for candidate matching', [
        'job_id' => $job->id
    ]);

    // Job title filter
    if ($job->job_title) {
        $filters['job_title'] = $job->job_title;
        \Log::debug('Added job_title filter', [
            'job_id' => $job->id,
            'job_title' => $job->job_title
        ]);
    } else {
        \Log::debug('No job_title filter applied', [
            'job_id' => $job->id
        ]);
    }

    // Apply scope filter
    \Log::debug('Applying scope filter', [
        'job_id' => $job->id,
        'filters' => $filters
    ]);
    $query->filter($filters);

    // Fallback: Directly apply job_title filter if scope fails
    if (isset($filters['job_title'])) {
        $query->where('job_title', $filters['job_title']);
        \Log::debug('Applied direct job_title filter as fallback', [
            'job_id' => $job->id,
            'job_title' => $filters['job_title']
        ]);
    }

    // Log the raw SQL query for debugging
    $sql = $query->toSql();
    $bindings = $query->getBindings();
    \Log::debug('Generated SQL query for matching candidates', [
        'job_id' => $job->id,
        'sql' => $sql,
        'bindings' => $bindings
    ]);

    // Log sample matched candidates for debugging
    $sampleMatches = $query->select('id', 'full_name', 'job_title')->take(10)->get();
    \Log::debug('Sample matched candidates', [
        'job_id' => $job->id,
        'sample_matches' => $sampleMatches->map(function ($candidate) {
            return [
                'candidate_id' => $candidate->id,
                'full_name' => $candidate->full_name,
                'job_title' => $candidate->job_title
            ];
        })->toArray()
    ]);

    \Log::info('Completed getMatchingCandidates query setup', [
        'job_id' => $job->id,
        'filters_applied' => array_keys($filters)
    ]);

    return $query;
}

public function dashboard($id)
{
    $employer = Auth::guard('employer-api')->user();
    \Log::info('Entering dashboard', [
        'job_id' => $id,
        'employer_id' => $employer->id
    ]);

    try {
        // Fetch the job with related employer and company
        \Log::debug('Fetching job with relations', [
            'job_id' => $id,
            'employer_id' => $employer->id
        ]);
        $job = JobPosting::with(['employer'])->findOrFail($id);
        \Log::info('Job retrieved', [
            'job_id' => $id,
            'employer_id' => $employer->id,
            'job_title' => $job->job_title,
        ]);

        // Verify the authenticated employer owns this job
        $currentUserId = $employer->id;
        if ($currentUserId != $job->employer_id) {
            \Log::warning('Unauthorized access attempt to job dashboard', [
                'job_id' => $id,
                'employer_id' => $currentUserId,
                'job_employer_id' => $job->employer_id
            ]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access',
            ], 403);
        }

        // Get matching candidates (no limit)
        \Log::debug('Fetching matching candidates', [
            'job_id' => $id,
            'employer_id' => $currentUserId
        ]);
        $matchesQuery = $this->getMatchingCandidates($job);
        $matches = $matchesQuery->select('id', 'full_name', 'email', 'city', 'job_title','experience_level')
            ->get();
        \Log::info('Matching candidates retrieved', [
            'job_id' => $id,
            'employer_id' => $currentUserId,
            'match_count' => $matches->count()
        ]);

        // Log matched candidate details (without sensitive data)
        if ($matches->count() > 0) {
            \Log::debug('Matched candidate details', [
                'job_id' => $id,
                'candidates' => $matches->map(function ($candidate) {
                    return [
                        'candidate_id' => $candidate->id,
                        'full_name' => $candidate->full_name,
                        'job_title' => $candidate->job_title,
                        'experience_level' => $candidate->experience_level


                        //experience_level
                    ];
                })->toArray()
            ]);
        } else {
            \Log::warning('No candidates matched', [
                'job_id' => $id,
                'job_title' => $job->job_title,
          
            ]);

            // Log sample candidate data for debugging
            $sampleCandidates = Candidate::select('id', 'job_title')
                ->get();
            \Log::debug('Sample candidate data for debugging', [
                'job_id' => $id,
                'sample_candidates' => $sampleCandidates->map(function ($candidate) {
                    return [
                        'candidate_id' => $candidate->id,
                        'job_title' => $candidate->job_title,
                        
                    ];
                })->toArray()
            ]);
        }

        // Get applications with candidate details
        \Log::debug('Fetching applications with candidate details', [
            'job_id' => $id,
            'employer_id' => $currentUserId
        ]);
        $applications = JobPostingApplication::where('job_posting_id', $id)
            ->with(['candidate' => function ($query) {
                $query->select('id', 'full_name', 'email', 'city', 'job_title','experience_level');
            }])
            ->get();
        \Log::info('Applications retrieved', [
            'job_id' => $id,
            'employer_id' => $currentUserId,
            'application_count' => $applications->count()
        ]);

        // Log successful data retrieval
        \Log::info('Job dashboard data retrieved successfully', [
            'job_id' => $id,
            'employer_id' => $currentUserId,
            'match_count' => $matches->count(),
            'application_count' => $applications->count()
        ]);

        return response()->json([
            'status' => 'success',
            'data' => [
                'job' => $job,
                'matches' => $matches,
                'applications' => $applications,
            ],
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        \Log::error('Job not found in dashboard', [
            'job_id' => $id,
            'employer_id' => $employer->id,
            'error' => $e->getMessage()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Job not found',
        ], 404);
    } catch (\Exception $e) {
        \Log::error('Failed to fetch job dashboard data', [
            'job_id' => $id,
            'employer_id' => $employer->id,
            'error' => $e->getMessage()
        ]);
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to fetch job data: ' . $e->getMessage(),
        ], 500);
    }
}

    public function store(Request $request): JsonResponse
{
    // Define validation rules
    $rules = [
        'employer_id' => 'required|exists:employers,id',
        'company_id' => 'nullable|exists:companies,id',
        'company_name' => 'nullable|string|max:255',
        'pan_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',

        'job_title' => 'required|string|max:255',
        'job_type' => 'required|in:Full-Time,Part-Time,Freelance,Contract,Internship',
        'location' => 'required|string|max:255',
        'work_location_type' => 'required|in:Work from Home,Work from Office,Hybrid',
        'compensation' => 'nullable|string|max:255',
        'min_salary' => 'required|numeric|min:0',
        'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
        'incentive' => 'nullable|numeric|min:0',
        'pay_type' => 'required|in:Salary,Salary + Incentive,Hourly,Per Project',
        'basic_requirements' => 'nullable|string',
        'additional_requirements' => 'nullable|json',
        'is_walkin_interview' => 'boolean',
        'joining_fee' => 'required|boolean',
        'joining_fee_required' => 'string|in:Yes,No',
        'communication_preference' => 'required',
        'total_experience_required' => 'nullable',
        'total_experience_max' => 'nullable|integer|min:0',
        'other_job_titles' => 'nullable|json',
        'degree_specialization' => 'nullable|json',
        'job_description' => 'nullable|string',
        'job_expire_time' => 'integer|min:1',
        'number_of_candidates_required' => 'integer|min:1',
        'english_level' => 'nullable|in:Beginner,Intermediate,Advanced,Fluent',
        'gender_preference' => 'nullable|in:No Preference,Male,Female,Other',
        'perks' => 'nullable|json',
        'preferred_roles' => 'nullable|json',
        'key_responsibilities' => 'nullable|string',
        'required_skills' => 'nullable|json',
        'interview_location' => 'nullable|string|max:255',
        'interview_mode' => 'nullable|in:Online,In-Person,Hybrid',
        'contact_email' => 'nullable|max:255',
        'contact_phone' => 'nullable|string|max:20',
        'not_email' => 'boolean',
        'viewed_number' => 'boolean',
        'industry' => 'nullable|string|max:255',
        'department' => 'nullable|string|max:255',
        'job_role' => 'required|string|max:255',
    ];

    $messages = [
        'pan_card.mimes' => 'The PAN card must be a PDF, JPG, JPEG, or PNG file.',
        'job_role.required' => 'The job role field is required.',
        'min_salary.required' => 'The minimum salary is required.',
        'max_salary.gte' => 'The maximum salary must be greater than or equal to the minimum salary.',
        'incentive.numeric' => 'The incentive must be a valid number.',
        'total_experience_required.in' => 'Total experience must be one of: Any, Fresher, 1-3 years, 3-5 years, 5+ years.',
        'joining_fee_required.in' => 'Joining fee required must be either Yes or No.',
    ];

    $validator = Validator::make($request->all(), $rules, $messages);

    // Ensure either company_id or company_name is provided
    if (!$request->company_id && !$request->company_name) {
        $validator->errors()->add('company_id', 'Either company_id or company_name is required.');
    }

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors(),
        ], 422);
    }

    try {
        $employer = Auth::guard('employer-api')->user();

        if (!$employer) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        // ✅ Check Job Post Credits (not general credits)
        $minimumCreditsRequired = 1;
        if (!$employer->hasEnoughCredits($minimumCreditsRequired, 'job_post')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Insufficient job post credits. Minimum 1 credit required to post a job.',
                'current_job_post_credits' => $employer->job_post_credits,
            ], 403);
        }

        // ✅ Handle company creation
        $company_id = $request->company_id;
        if ($request->company_name) {
            $otherCertificatePath = null;

            if ($request->hasFile('pan_card') && $request->file('pan_card')->isValid()) {
                $filename = 'pan_' . time() . '_' . $request->file('pan_card')->getClientOriginalName();
                $otherCertificatePath = $request->file('pan_card')->storeAs('documents', $filename, 'public');
            }

            $company = Company::create([
                'employer_id' => $employer->id,
                'name' => $request->company_name,
                'company_location' => $request->location,
                'contact_person' => null,
                'contact_phone' => $request->contact_phone,
                'other_certificate' => $otherCertificatePath,
                'is_approved' => false,
            ]);

            $company_id = $company->id;
        } else {
            $company = Company::where('id', $request->company_id)
                ->where('employer_id', $employer->id)
                ->first();

            if (!$company) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or unauthorized company.',
                ], 422);
            }

            $company_id = $company->id;
        }

        // ✅ Create the job post
        $jobPosting = JobPosting::create([
            'employer_id' => $employer->id,
            'company_id' => $company_id,
            'job_title' => $request->job_title,
            'job_type' => $request->job_type,
            'location' => $request->location,
            'work_location_type' => $request->work_location_type,
            'compensation' => $request->compensation,
            'min_salary' => $request->min_salary,
            'max_salary' => $request->max_salary,
            'incentive' => $request->incentive,
            'pay_type' => $request->pay_type,
            'basic_requirements' => $request->basic_requirements,
            'additional_requirements' => $request->additional_requirements,
            'is_walkin_interview' => $request->is_walkin_interview ?? false,
            'joining_fee' => $request->joining_fee,
            'joining_fee_required' => $request->joining_fee_required,
            'communication_preference' => $request->communication_preference,
            'total_experience_required' => $request->total_experience_required,
            'total_experience_max' => $request->total_experience_max,
            'other_job_titles' => $request->other_job_titles,
            'degree_specialization' => $request->degree_specialization,
            'job_description' => $request->job_description,
            'job_expire_time' => $request->job_expire_time ?? 7,
            'number_of_candidates_required' => $request->number_of_candidates_required ?? 1,
            'english_level' => $request->english_level,
            'gender_preference' => $request->gender_preference,
            'perks' => $request->perks,
            'preferred_roles' => $request->preferred_roles,
            'key_responsibilities' => $request->key_responsibilities,
            'required_skills' => $request->required_skills,
            'interview_location' => $request->interview_location,
            'interview_mode' => $request->interview_mode,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'not_email' => $request->not_email ?? false,
            'viewed_number' => $request->viewed_number ?? false,
            'is_verified' => false,
            'industry' => $request->industry,
            'department' => $request->department,
            'job_role' => $request->job_role,
        ]);


        $employer->deductCredits(1, 'job_post');

        return response()->json([
            'status' => 'success',
            'message' => 'Job post created successfully',
            'data' => $jobPosting,
            'remaining_job_post_credits' => $employer->job_post_credits,
        ], 201);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Failed to create job post',
            'error' => $e->getMessage(),
        ], 500);
    }
}



    public function getByEmployer($employerId): JsonResponse
{
    $jobs = JobPosting::with(['company', 'employer'])
        ->where('employer_id', $employerId)
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $jobs
    ]);
}

 public function index(Request $request): JsonResponse
 {
       
    $query = JobPosting::query();

        if ($request->filled('job_title')) {
                    $query->where('job_title', 'like', '%' . $request->job_title . '%');
        
        }

        if ($request->filled('total_experience_required')) {
            $exp = array_map('trim', explode(',', $request->total_experience_required));
            $query->whereIn('total_experience_required', $exp);
        }

        if ($request->filled('work_location_type')) {
            $locations = array_map('trim', explode(',', $request->work_location_type));
            $query->whereIn('work_location_type', $locations);
        }

        if ($request->filled('categories')) {
            $categories = array_map('trim', explode(',', $request->categories));
            $query->whereIn('category', $categories);
        }

        if ($request->filled('job_type')) {
            $types = array_map('trim', explode(',', $request->job_type));
            $query->whereIn('job_type', $types);
        }
        if ($request->has('date_posted')) {
            switch ($request->date_posted) {
                case 'last_3_days':
                    $query->where('created_at', '>=', Carbon::now()->subDays(3));
                    break;
                case 'last_10_days':
                    $query->where('created_at', '>=', Carbon::now()->subDays(10));
                    break;
                case 'last_30_days':
                    $query->where('created_at', '>=', Carbon::now()->subDays(30));
                    break;
            }
        }

        
    //     // Paginate results
        $jobs = $query->latest()->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $jobs
        ]);
}


      public function refreshJob($jobId): JsonResponse
    {
        try {
            $employer = Auth::guard('employer-api')->user();

            if (!$employer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $jobPosting = JobPosting::where('id', $jobId)
                ->where('employer_id', $employer->id)
                ->first();

            if (!$jobPosting) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job posting not found or unauthorized',
                ], 404);
            }

            // Check if employer has enough credits
            $minimumCreditsRequired = 1;
            if (!$employer->hasEnoughCredits($minimumCreditsRequired)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient credits. Minimum 1 credit required to refresh a job.',
                    'current_credits' => $employer->credits,
                ], 403);
            }

            // Update created_at to current date
            $jobPosting->created_at = Carbon::now();
            $jobPosting->save();

            // Deduct 1 credit
            $employer->deductCredits(1);

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting refreshed successfully',
                'data' => $jobPosting,
                'remaining_credits' => $employer->credits,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh job posting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $jobId): JsonResponse
    {
        // Use same validation rules as store method
        $rules = [
            'company_id' => 'nullable|exists:companies,id',
            'company_name' => 'nullable|string|max:255',
            'pan_card' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'job_title' => 'required|string|max:255',
            'job_type' => 'required|in:Full-Time,Part-Time,Freelance,Contract,Internship',
            'location' => 'required|string|max:255',

            'work_location_type' => 'required|in:Work from Home,Work from Office,Hybrid',
            'compensation' => 'nullable|string|max:255',
            'min_salary' => 'required|numeric|min:0',
            'max_salary' => 'nullable|numeric|min:0|gte:min_salary',
            'incentive' => 'nullable|numeric|min:0',
            'pay_type' => 'required|in:Salary,Salary + Incentive,Hourly,Per Project',
            'basic_requirements' => 'nullable|string',
            'additional_requirements' => 'nullable|json',
            'is_walkin_interview' => 'boolean',
            'joining_fee' => 'required|boolean',
            'joining_fee_required' => 'string|in:Yes,No',
            'communication_preference' => 'required',
            'total_experience_required' => 'nullable',
            'total_experience_max' => 'nullable|integer|min:0',
            'other_job_titles' => 'nullable|json',
            'degree_specialization' => 'nullable|json',
            'job_description' => 'nullable|string',
            'job_expire_time' => 'integer|min:1',
            'number_of_candidates_required' => 'integer|min:1',
            'english_level' => 'nullable|in:Beginner,Intermediate,Advanced,Fluent',
            'gender_preference' => 'nullable|in:No Preference,Male,Female,Other',
            'perks' => 'nullable|json',
            'preferred_roles' => 'nullable|json',
            'key_responsibilities' => 'nullable|string',
            'required_skills' => 'nullable|json',
            'interview_location' => 'nullable|string|max:255',
            'interview_mode' => 'nullable|in:Online,In-Person,Hybrid',
            'contact_email' => 'nullable|email|max:255',
            'contact_phone' => 'nullable|string|max:20',

            'not_email' => 'boolean',
            'viewed_number' => 'boolean',
            'industry' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'job_role' => 'required|string|max:255',
        ];

        $messages = [
            'pan_card.mimes' => 'The PAN card must be a PDF, JPG, JPEG, or PNG file.',
            'job_role.required' => 'The job role field is required.',
            'min_salary.required' => 'The minimum salary is required.',
            'max_salary.gte' => 'The maximum salary must be greater than or equal to the minimum salary.',
            'incentive.numeric' => 'The incentive must be a valid number.',
            'total_experience_required.in' => 'Total experience must be one of: Any, Fresher, 1-3 years, 3-5 years, 5+ years.',
            'joining_fee_required.in' => 'Joining fee required must be either Yes or No.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if (!$request->company_id && !$request->company_name) {
            $validator->errors()->add('company_id', 'Either company_id or company_name is required.');
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $employer = Auth::guard('employer-api')->user();

            if (!$employer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $jobPosting = JobPosting::where('id', $jobId)
                ->where('employer_id', $employer->id)
                ->first();

            if (!$jobPosting) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job posting not found or unauthorized',
                ], 404);
            }

            // Check if employer has enough credits
            $minimumCreditsRequired = 1;
            if (!$employer->hasEnoughCredits($minimumCreditsRequired)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient credits. Minimum 1 credit required to update a job.',
                    'current_credits' => $employer->credits,
                ], 403);
            }

            // Handle company creation or validation
            $company_id = $request->company_id;
            if ($request->company_name) {
                $otherCertificatePath = null;
                if ($request->hasFile('pan_card') && $request->file('pan_card')->isValid()) {
                    $filename = 'pan_' . time() . '_' . $request->file('pan_card')->getClientOriginalName();
                    $otherCertificatePath = $request->file('pan_card')->storeAs('documents', $filename, 'public');
                }

                $company = Company::create([
                    'employer_id' => $employer->id,
                    'name' => $request->company_name,
                    'company_location' => $request->location,
                    'contact_person' => null,
                    'contact_phone' => $request->contact_phone,
                    'other_certificate' => $otherCertificatePath,
                    'is_approved' => false,
                ]);

                $company_id = $company->id;
            } else {
                $company = Company::where('id', $request->company_id)->where('employer_id', $employer->id)->first();
                if (!$company) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Invalid or unauthorized company.',
                    ], 422);
                }
                $company_id = $company->id;
            }

        
            // Update the job post
            $jobPosting->update([
                'company_id' => $company_id,
                'job_title' => $request->job_title,
                'job_type' => $request->job_type,
                'location' => $request->location,
            
                'work_location_type' => $request->work_location_type,
                'compensation' => $request->compensation,
                'min_salary' => $request->min_salary,
                'max_salary' => $request->max_salary,
                'incentive' => $request->incentive,
                'pay_type' => $request->pay_type,
                'basic_requirements' => $request->basic_requirements,
                'additional_requirements' => $request->additional_requirements,
                'is_walkin_interview' => $request->is_walkin_interview ?? false,
                'joining_fee' => $request->joining_fee,
                'joining_fee_required' => $request->joining_fee_required,
                'communication_preference' => $request->communication_preference,
                'total_experience_required' => $request->total_experience_required,
                'total_experience_max' => $request->total_experience_max,
                'other_job_titles' => $request->other_job_titles,
                'degree_specialization' => $request->degree_specialization,
                'job_description' => $request->job_description,
                'job_expire_time' => $request->job_expire_time ?? 7,
                'number_of_candidates_required' => $request->number_of_candidates_required ?? 1,
                'english_level' => $request->english_level,
                'gender_preference' => $request->gender_preference,
                'perks' => $request->perks,
                'preferred_roles' => $request->preferred_roles,
                'key_responsibilities' => $request->key_responsibilities,
                'required_skills' => $request->required_skills,
                'interview_location' => $request->interview_location,
                'interview_mode' => $request->interview_mode,
                'contact_email' => $request->contact_email,
                'contact_phone' => $request->contact_phone,
      
                'not_email' => $request->not_email ?? false,
                'viewed_number' => $request->viewed_number ?? false,
                'industry' => $request->industry,
                'department' => $request->department,
                'job_role' => $request->job_role,
            ]);

            // Deduct 1 credit
            $employer->deductCredits(1);

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting updated successfully',
                'data' => $jobPosting,
                'remaining_credits' => $employer->credits,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update job posting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($jobId): JsonResponse
    {
        try {
            $employer = Auth::guard('employer-api')->user();

            if (!$employer) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthorized',
                ], 401);
            }

            $jobPosting = JobPosting::where('id', $jobId)
                ->where('employer_id', $employer->id)
                ->first();

            if (!$jobPosting) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Job posting not found or unauthorized',
                ], 404);
            }

            // ✅ Just delete the job posting, no credit check or deduction
            $jobPosting->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Job posting deleted successfully',
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete job posting',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    
     public function getJobTitles(Request $request): JsonResponse
    {
        $search = $request->query('search', '');
        $limit = 20; // Default limit of 20 job titles

        $jobTitles = JobPosting::query()
            ->when($search, function ($query, $search) {
                return $query->where('job_title', 'like', '%' . $search . '%');
            }, function ($query) use ($limit) {
                return $query->distinct()->take($limit);
            })
            ->distinct()
            ->pluck('job_title')
            ->values();

        return response()->json([
            'status' => 'success',
            'data' => $jobTitles
        ]);
    }
    
private function applyFilters($query, Request $request): void
{
    if ($request->has('job_type')) {
        $query->where('job_type', $request->job_type);
    }

    if ($request->has('location')) {
        $query->where('location', 'like', '%' . $request->location . '%');
    }

    if ($request->has('work_location_type')) {
        $query->where('work_location_type', $request->work_location_type);
    }

    if ($request->has('pay_type')) {
        $query->where('pay_type', $request->pay_type);
    }

    if ($request->has('is_walkin_interview')) {
        $query->where('is_walkin_interview', $request->is_walkin_interview);
    }

    if ($request->has('total_experience_required')) {
        $query->where('total_experience_required', '<=', $request->total_experience_required);
    }

    if ($request->has('date_posted')) {
        switch ($request->date_posted) {
            case 'last_3_days':
                $query->where('created_at', '>=', now()->subDays(3));
                break;
            case 'last_10_days':
                $query->where('created_at', '>=', now()->subDays(10));
                break;
            case 'last_30_days':
                $query->where('created_at', '>=', now()->subDays(30));
                break;
        }
    }

  
}


private function formatSalary($salary)
{
    if (!$salary || $salary == 0) return null;
    if ($salary >= 1000000) {
        return round($salary / 1000000, 1) . "M";
    }
    if ($salary >= 1000) {
        return round($salary / 1000, 1) . "k";
    }
    return $salary;
}
 

 public function show($slug)
{
    // Find the job posting by slug or fail with a 404 error
    $job = JobPosting::with(['company', 'employer'])->where('slug', $slug)->firstOrFail();

    $formatted = [
        "Job Details" => [
            "Job Role"        => $job->job_role,
            "Job Title"       => $job->job_title,
            "Work Location"   => $job->location,
            "Department"      => $job->department,
            "Role / Category" => $job->job_role ?? "Not specified",
            "Employment Type" => $job->job_type,
            "Shift"           => "Day Shift", // make dynamic if you want
            "Pay Type"        => $job->pay_type,
            "Min Salary"      => $this->formatSalary($job->min_salary),
            "Max Salary"      => $this->formatSalary($job->max_salary),
            "Incentive"       => $this->formatSalary($job->incentive),
        ],
        "About Company" => [
            "Name"          => $job->company->name ?? null,
            "Address"       => $job->location,
            "Contact Email" => $job->contact_email,
            "Contact Phone" => $job->contact_phone,
        ],
        "Requirements" => [
            "Basic Requirements"     => $job->basic_requirements !== "null" ? $job->basic_requirements : null,
            "Additional Requirements"=> json_decode($job->additional_requirements, true),
            "Degree Specialization"  => array_filter(json_decode($job->degree_specialization, true) ?? []),
            "English Level"          => $job->english_level,
            "Gender Preference"      => $job->gender_preference,
            "Perks"                  => json_decode($job->perks, true),
        ],
    ];

    return response()->json([
        "job"       => $job,
        "formatted" => $formatted
    ]);
}





}