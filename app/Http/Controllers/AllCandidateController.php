<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mockery\Undefined;
use Illuminate\Support\Facades\Auth;

class AllCandidateController extends Controller
{
    //



    public function index()
    {
        $candidates = Candidate::all();
        return response()->json($candidates);
    }

    public function AddCandidateInfo(Request $request, $token) {
    // Find the candidate by token
    $candidate = Auth::guard('candidate-api')->user();

    // Check if candidate exists
    if (!$candidate) {
        return response()->json([
            "success" => false,
            "message" => "Candidate not found"
        ], 404);
    }

    // Validate the request
    $validated = $request->validate([
        'full_name' => 'sometimes|string|max:255',
        'number' => 'sometimes|string|max:20',
        'dob' => 'sometimes|date',
        'gender' => 'sometimes|string|in:Male,Female,Other',
        'email' => 'sometimes|email|max:255',
        'address' => 'sometimes|string|nullable',
        'city' => 'sometimes|string|nullable',
        'state' => 'sometimes|string|nullable',
        'degree' => 'sometimes|string|nullable',
        'specialization' => 'sometimes|string|nullable',
        'college_name' => 'sometimes|string|nullable',
        'passing_marks' => 'sometimes|numeric|nullable',
        'pursuing' => 'sometimes|boolean|nullable',
        'experience_years' => 'sometimes|integer|nullable',
        'experience_months' => 'sometimes|integer|nullable',
        'job_title' => 'sometimes|string|nullable',
        'job_roles' => 'sometimes|string|nullable',
        'company_name' => 'sometimes|string|nullable',
        'current_salary' => 'sometimes|numeric|nullable',
        // 'start_year' => 'sometimes|integer|digits:4|nullable',
        // 'start_month' => 'sometimes|string|max:20|nullable',
        'prefers_night_shift' => 'sometimes|boolean',
        'prefers_day_shift' => 'sometimes|boolean',
        'work_from_home' => 'sometimes|boolean',
        'work_from_office' => 'sometimes|boolean',
        'skills' => 'sometimes|array|nullable',
        'preferred_language' => 'sometimes|string|nullable',
        'password' => 'sometimes|string|min:8',
        'education_level' => 'sometimes|string|nullable',
        'currently_pursuing' => 'sometimes|string|nullable',
        'highest_education' => 'sometimes|string|nullable',
        'complete_years' => 'sometimes|integer|nullable',
        'complete_month' => 'sometimes|string|nullable',
        'school_medium' => 'sometimes|string|nullable',
        'experience_level' => 'sometimes|string|nullable',
        'is_working' => 'sometimes|string|nullable',
        'notice_period' => 'sometimes|string|nullable',
        'preferred_job_titles' => 'sometimes|string|nullable',
    ]);

    // Update candidate properties
    $candidate->full_name = $request->full_name ?? $candidate->full_name;
    $candidate->number = $request->number ?? $candidate->number;
    $candidate->dob = $request->dob ?? $candidate->dob;
    $candidate->gender = $request->gender ?? $candidate->gender;
    $candidate->email = $request->email ?? $candidate->email;
    $candidate->address = $request->address ?? $candidate->address;
    $candidate->city = $request->city ?? $candidate->city;
    $candidate->state = $request->state ?? $candidate->state;
    $candidate->degree = $request->degree ?? $candidate->degree;
    $candidate->specialization = $request->specialization ?? $candidate->specialization;
    $candidate->college_name = $request->college_name ?? $candidate->college_name;
    $candidate->passing_marks = $request->passing_marks ?? $candidate->passing_marks;
    $candidate->pursuing = $request->pursuing ?? $candidate->pursuing;
    $candidate->experience_years = $request->experience_years ?? $candidate->experience_years;
    $candidate->experience_months = $request->experience_months ?? $candidate->experience_months;
    $candidate->job_title = $request->job_title ?? $candidate->job_title;
    $candidate->job_roles = $request->job_roles ?? $candidate->job_roles;
    $candidate->company_name = $request->company_name ?? $candidate->company_name;
    $candidate->current_salary = $request->current_salary ?? $candidate->current_salary;
    // $candidate->start_year = $request->start_year ?? $candidate->start_year;
    // $candidate->start_month = $request->start_month ?? $candidate->start_month;
    $candidate->prefers_night_shift = $request->prefers_night_shift ?? $candidate->prefers_night_shift;
    $candidate->prefers_day_shift = $request->prefers_day_shift ?? $candidate->prefers_day_shift;
    $candidate->work_from_home = $request->work_from_home ?? $candidate->work_from_home;
    $candidate->work_from_office = $request->work_from_office ?? $candidate->work_from_office;
    $candidate->skills = is_array($request->skills) ? json_encode($request->skills) : $candidate->skills;
    $candidate->preferred_language = $request->preferred_language ?? $candidate->preferred_language;
    $candidate->education_level = $request->education_level ?? $candidate->education_level;
    $candidate->currently_pursuing = $request->currently_pursuing ?? $candidate->currently_pursuing;
    $candidate->highest_education = $request->highest_education ?? $candidate->highest_education;
    $candidate->complete_years = $request->complete_years ?? $candidate->complete_years;
    $candidate->complete_month = $request->complete_month ?? $candidate->complete_month;
    $candidate->school_medium = $request->school_medium ?? $candidate->school_medium;
    $candidate->experience_level = $request->experience_level ?? $candidate->experience_level;
    $candidate->is_working = $request->is_working ?? $candidate->is_working;
    $candidate->notice_period = $request->notice_period ?? $candidate->notice_period;
    $candidate->preferred_job_titles = is_array($request->preferred_job_titles) ? json_encode($request->preferred_job_titles) : $candidate->preferred_job_titles;
    $candidate->password = $request->password ? Hash::make($request->password) : $candidate->password;
    $candidate->doneprofile = 1;

    // Handle resume file upload
    $path = null;
    if ($request->hasFile("resume")) {
        // Delete old resume if it exists
        if ($candidate->resume) {
            Storage::disk("public")->delete($candidate->resume);
        }
        // Store new resume
        $path = $request->file('resume')->store('pdf', "public");
    }
    $candidate->resume = $path ?? $candidate->resume; // Keep old resume if no new file uploaded

    // Save the candidate
    $candidate->save();

    return response()->json([
        "success" => true,
        "message" => "Candidate profile updated successfully",
        "path" => $path
    ]);
}

 



    public function getCandidateinfo($token){
       $candidate = Auth::guard('candidate-api')->user();
   
    if(!$candidate){
    return response()->json(["success"=>false]);

    }
    return response()->json(["success"=>true,"candidate"=>$candidate]);

    }


 public function loginbypasswod(Request $request){
$email=$request->email;
$getuser= Candidate::whereEmail($email)->first();

 if (!$getuser || !Hash::check($request->password, $getuser->password)) {
        return response()->json([
            "success" => false,
            "message" => "Invalid email or password"
        ]);
    }
    
return response()->json(["success"=>true,"message"=>"user Logined","token"=>$getuser->token]);

 }



 public function CreateCandidate(Request $request)
{
    // Validate the request data
    $validated = $request->validate([
        'full_name' => 'required|string|max:255',
        'email' => 'required|email|unique:candidates,email',
        'password' => 'required|string|min:6',
        'number' => 'nullable|string|max:20',
        'dob' => 'nullable|date',
        'gender' => 'nullable|string|in:male,female,other',
        'address' => 'nullable|string',
        'city' => 'nullable|string',
        'state' => 'nullable|string',
        'degree' => 'nullable|string',
        'specialization' => 'nullable|string',
        'college_name' => 'nullable|string',
        'passing_marks' => 'nullable|numeric',
        'pursuing' => 'nullable|boolean',
        'experience_years' => 'nullable|integer',
        'experience_months' => 'nullable|integer',
        'job_title' => 'nullable|string',
        'job_roles' => 'nullable|string',
        'company_name' => 'nullable|string',
        'current_salary' => 'nullable|numeric',
        'start_date' => 'nullable|date',
        'prefers_night_shift' => 'nullable|boolean',
        'prefers_day_shift' => 'nullable|boolean',
        'work_from_home' => 'nullable|boolean',
        'work_from_office' => 'nullable|boolean',
        'skills' => 'nullable|string',
        'preferred_language' => 'nullable|string',
        'resume' => 'nullable|file|mimes:pdf|max:2048', // Max 2MB PDF file
    ]);

    // Create a new candidate
    $candidate = new Candidate();
    $candidate->token = Str::uuid()->toString(); // Generate unique token
    $candidate->full_name = $request->full_name;
    $candidate->email = $request->email;
    $candidate->password = Hash::make($request->password);
    $candidate->number = $request->number;
    $candidate->dob = $request->dob;
    $candidate->gender = $request->gender;
    $candidate->address = $request->address;
    $candidate->city = $request->city;
    $candidate->state = $request->state;
    $candidate->degree = $request->degree;
    $candidate->specialization = $request->specialization;
    $candidate->college_name = $request->college_name;
    $candidate->passing_marks = $request->passing_marks;
    $candidate->pursuing = $request->pursuing;
    $candidate->experience_years = $request->experience_years;
    $candidate->experience_months = $request->experience_months;
    $candidate->job_title = $request->job_title;
    $candidate->job_roles = $request->job_roles;
    $candidate->company_name = $request->company_name;
    $candidate->current_salary = $request->current_salary;
    $candidate->start_date = $request->start_date;
    $candidate->prefers_night_shift = $request->prefers_night_shift;
    $candidate->prefers_day_shift = $request->prefers_day_shift;
    $candidate->work_from_home = $request->work_from_home;
    $candidate->work_from_office = $request->work_from_office;
    $candidate->skills = $request->skills;
    $candidate->preferred_language = $request->preferred_language;
    $candidate->doneprofile = 1;

    // Handle resume file upload
    if ($request->hasFile('resume')) {
        $path = $request->file('resume')->store('pdf', 'public');
        $candidate->resume = $path;
    }

    // Save the candidate
    $candidate->save();

    return response()->json([
        'success' => true,
        'message' => 'Candidate account created successfully',
        'token' => $candidate->token,
        'path' => $candidate->resume ?? null
    ], 201);
}


}
