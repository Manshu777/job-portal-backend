<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Candidate;
use App\Models\JobPosting;
use App\Models\JobPostingApplication;
use Illuminate\Support\Facades\Auth;

class JobApplicationController extends Controller
{
    public function apply(Request $request)
    {
        // Validate input
        $request->validate([
            'job_posting_id' => 'required|integer|exists:job_postings,id',
        ]);

        // Get authenticated candidate (assuming API authentication with Sanctum)
        $candidate = Auth::guard('candidate-api')->user();
        
        if (!$candidate) {
            return response()->json([
                "success" => false,
                "message" => "Candidate not found"
            ], 404);
        }

        // Fetch job posting
        $jobPosting = JobPosting::findOrFail($request->job_posting_id);

        // Calculate candidate's total experience in months
        $candidateExpMonths = ($candidate->experience_years ?? 0) * 12 + ($candidate->experience_months ?? 0);

        // Assume job's total_experience_required is in years; convert to months
        $requiredExpYears = (int) ($jobPosting->total_experience_required ?? 0);
        $requiredExpMonths = $requiredExpYears * 12;

        // Check experience requirement
        if ($candidateExpMonths < $requiredExpMonths) {
            return response()->json(['error' => 'Insufficient experience for this job'], 403);
        }

        // Check if already applied
        $existingApplication = JobPostingApplication::where('candidate_id', $candidate->id)
            ->where('job_posting_id', $jobPosting->id)
            ->first();

        if ($existingApplication) {
            return response()->json(['error' => 'You have already applied for this job'], 409);
        }

        // Create the application
        JobPostingApplication::create([
          
             'candidate_id'=> $candidate->id,
            'job_posting_id' => $jobPosting->id,
            'status' => 'applied', // Use a valid status value
        ]);

        return response()->json(['message' => 'Successfully applied for the job'], 201);
    }
    public function getAppliedJobs(Request $request)
{
    $candidate = Auth::guard('candidate-api')->user();
    
    if (!$candidate) {
        return response()->json([
            "success" => false,
            "message" => "Candidate not found"
        ], 404);
    }

    $appliedJobIds = JobPostingApplication::where('candidate_id', $candidate->id)
        ->pluck('job_posting_id')
        ->toArray();

    return response()->json([
        "success" => true,
        "appliedJobIds" => $appliedJobIds
    ], 200);
}
}