<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateEducation;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mockery\Undefined;
use Illuminate\Support\Facades\Auth;
use App\Models\CandidateExperience;
use Illuminate\Support\Facades\Validator;
class AllCandidateController extends Controller
{
    //



    public function index()
    {
        $candidates = Candidate::all();
        return response()->json($candidates);
    }

    public function AddCandidateInfo(Request $request, $token)
{
    // Get authenticated candidate
    $candidate = Auth::guard('candidate-api')->user();

    if (!$candidate) {
        return response()->json([
            'success' => false,
            'message' => 'Candidate not found'
        ], 404);
    }

    // === VALIDATION (Accept JSON strings for experiences[]) ===
    $request->validate([
        // Personal Info
        'full_name'           => 'sometimes|string|max:255',
        'number'              => 'sometimes|string|max:20',
        'dob'                 => 'sometimes|date',
        'gender'              => 'sometimes|in:Male,Female,Other',
        'email'               => 'sometimes|email|max:255',
        'address'             => 'sometimes|string|nullable',
        'city'                => 'sometimes|string|nullable',
        'state'               => 'sometimes|string|nullable',

        // Work Preferences
        'prefers_night_shift' => 'sometimes|boolean',
        'prefers_day_shift'   => 'sometimes|boolean',
        'work_from_home'      => 'sometimes|boolean',
        'work_from_office'    => 'sometimes|boolean',
        'preferred_language'  => 'sometimes|string|nullable',
        'english_level'       => 'sometimes|string|nullable',

        // Experience Summary
        'experience_years'    => 'nullable|integer|min:0|max:50',
        'experience_months'   => 'nullable|integer|min:0|max:11',
        'experience_level'    => 'sometimes|in:Fresher,Experienced,Both',
        'is_working'          => 'nullable|in:Yes,No',
        'notice_period'       => 'sometimes|string|nullable',
        'current_salary'      => 'sometimes|numeric|nullable',

        // === EXPERIENCES (as JSON strings via experiences[]) ===
        'experiences'         => 'sometimes|array',
        'experiences.*'       => 'sometimes|string', // Accept JSON string

        // Education (Flat)
        'currently_pursuing'  => 'nullable|string',
        'highest_education'   => 'nullable|string',

        // === NESTED EDUCATION BLOCKS ===
        'graduation'          => 'sometimes|json',
        'postGraduation'      => 'sometimes|json',

        // Arrays (JSON)
        'skills'               => 'nullable',
        'preferred_job_titles' => 'nullable',
        'preferred_languages'  => 'nullable',
        'preferred_locations'  => 'nullable',

        // Files
        'profile_pic' => 'nullable',
        'resume'      => 'nullable',

        // Password
        'password'    => 'sometimes|string|min:8',
    ]);
     

    // === PARSE NESTED EDUCATION DATA ===
    $graduation = $request->input('graduation', []);
    $postGraduation = $request->input('postGraduation', []);

    if (is_string($graduation)) {
        $graduation = json_decode($graduation, true) ?? [];
    }
    if (is_string($postGraduation)) {
        $postGraduation = json_decode($postGraduation, true) ?? [];
    }


     \Log::info('=== AddCandidateInfo Request - Candidate ID: ' . $candidate->id . ' ===', [
        'token'           => $token,
        'ip'              => $request->ip(),
        'user_agent'      => $request->userAgent(),
        'headers'         => $request->headers->all(),
        'all_input'       => $request->all(),                    // All input (including JSON decoded)
        'files'           => array_map(function ($file) {
            return [
                'name'         => $file->getClientOriginalName(),
                'size'         => $file->getSize(),
                'mime'         => $file->getMimeType(),
                'extension'    => $file->getClientOriginalExtension(),
            ];
        }, $request->allFiles()),
        'raw_content'     => $request->getContent(),              // Raw JSON/body if sent as application/json
    ]);

    // === UPDATE CANDIDATE (Flat Fields) ===
    $candidate->fill([
        'full_name'           => $request->full_name,
        'number'              => $request->number,
        'dob'                 => $request->dob,
        'gender'              => $request->gender,
        'email'               => $request->email,
        'address'             => $request->address,
        'city'                => $request->city,
        'state'               => $request->state,

        'currently_pursuing'  => $request->currently_pursuing,
        'highest_education'   => $request->highest_education,

        'experience_years'    => $request->experience_years,
        'experience_months'   => $request->experience_months,
        'experience_level'    => $request->experience_level,

        'prefers_night_shift' => $request->filled('prefers_night_shift') ? $request->prefers_night_shift : $candidate->prefers_night_shift,
        'prefers_day_shift'   => $request->filled('prefers_day_shift') ? $request->prefers_day_shift : $candidate->prefers_day_shift,
        'work_from_home'      => $request->filled('work_from_home') ? $request->work_from_home : $candidate->work_from_home,
        'work_from_office'    => $request->filled('work_from_office') ? $request->work_from_office : $candidate->work_from_office,

        'english_level'       => $request->english_level,
        'preferred_language'  => $request->preferred_language,
        'immediate_joiner' => $request->has('immediate_joiner') 
        ? $request->boolean('immediate_joiner') 
        : $candidate->immediate_joiner,
    'open_to_opportunities' => $request->has('open_to_opportunities')
        ? $request->boolean('open_to_opportunities')
        : $candidate->open_to_opportunities,

        // JSON Arrays
        'skills'              => $request->has('skills') ? json_encode($request->skills) : $candidate->skills,
        'preferred_job_titles'=> $request->has('preferred_job_titles') ? json_encode($request->preferred_job_titles) : $candidate->preferred_job_titles,
        'preferred_languages' => $request->has('preferred_languages') ? json_encode($request->preferred_languages) : $candidate->preferred_languages,
        'preferred_locations' => $request->has('preferred_locations') ? json_encode($request->preferred_locations) : $candidate->preferred_locations,
    ]);

    // === PASSWORD ===
    if ($request->filled('password')) {
        $candidate->password = Hash::make($request->password);
    }

    $candidate->doneprofile = 1;

    // === FILE: Profile Picture ===
    if ($request->hasFile('profile_pic')) {
        if ($candidate->profile_pic) {
            Storage::disk('public')->delete($candidate->profile_pic);
        }
        $candidate->profile_pic = $request->file('profile_pic')->store('images', 'public');
    }

    // === FILE: Resume ===
    if ($request->hasFile('resume')) {
        if ($candidate->resume) {
            Storage::disk('public')->delete($candidate->resume);
        }
        $candidate->resume = $request->file('resume')->store('pdf', 'public');
    }

    // === SAVE CANDIDATE ===
    $candidate->save();

    // === SAVE EDUCATION BLOCKS ===
    foreach (['graduation', 'postGraduation'] as $type) {
        $data = $type === 'graduation' ? $graduation : $postGraduation;
        $educationType = $type === 'graduation' ? 'graduation' : 'post_graduation';

        if (!empty($data['education_level'])) {
            CandidateEducation::updateOrCreate(
                [
                    'candidate_id' => $candidate->id,
                    'education_type' => $educationType,
                ],
                [
                    'education_level' => $data['education_level'] ?? null,
                    'specialization'  => $data['specialization'] ?? null,
                    'college_name'    => $data['college_name'] ?? null,
                    'complete_years'  => $data['complete_years'] ?? null,
                    'complete_month'  => $data['complete_month'] ?? null,
                    'school_medium'   => $data['school_medium'] ?? null,
                ]
            );
        }
    }

    // === HANDLE EXPERIENCES (JSON strings from experiences[]) ===
    $rawExperiences = $request->input('experiences', []);
    $experiences = collect($rawExperiences)
        ->map(fn($item) => is_string($item) ? json_decode($item, true) : $item)
        ->filter()
        ->values()
        ->all();

    if (!empty($experiences)) {
        // Delete old experiences
        $candidate->experiences()->delete();

        $currentJob = null;

        foreach ($experiences as $exp) {
            // Validate each experience
            $validator = Validator::make($exp, [
                'job_title'     => 'required|string|max:255',
                'company_name'  => 'required|string|max:255',
                'start_date'    => 'required|date_format:Y-m',
                'end_date'      => 'nullable|date_format:Y-m|after:start_date',
                'is_current'    => 'sometimes|boolean',
                'salary'        => 'nullable|numeric',
            ]);

            if ($validator->fails()) {
                continue; // skip invalid
            }

            $experience = $candidate->experiences()->create([
                'job_title'     => $exp['job_title'],
                'company_name'  => $exp['company_name'],
                'start_date'    => $exp['start_date'],
                'end_date'      => $exp['is_current'] ?? false ? null : ($exp['end_date'] ?? null),
                'is_current'    => $exp['is_current'] ?? false,
                'salary'        => $exp['salary'] ?? null,
            ]);

            if ($exp['is_current'] ?? false) {
                $currentJob = $experience;
            }
        }

        // === UPDATE SUMMARY FIELDS FROM CURRENT JOB ===
        if ($currentJob) {
            $candidate->update([
                'job_title'       => $currentJob->job_title,
                'company_name'    => $currentJob->company_name,
                'current_salary'  => $currentJob->salary,
                'is_working'      => 'Yes',
            ]);
        } else {
            $candidate->update([
                'job_title'       => null,
                'company_name'    => null,
                'current_salary'  => null,
                'is_working'      => 'No',
            ]);
        }
    }

    // === SYNC NOTICE PERIOD ===
    if ($candidate->is_working === 'Yes' && $request->filled('notice_period')) {
        $candidate->notice_period = $request->notice_period;
        $candidate->save();
    }

    // === RESPONSE ===
    return response()->json([
        'success' => true,
        'message' => 'Candidate profile updated successfully',
        'data' => [
            'candidate' => $candidate->only([
                'id', 'full_name', 'email', 'highest_education', 'experience_level', 'doneprofile'
            ]),
            'experiences' => $candidate->experiences->map->only([
                'job_title', 'company_name', 'start_date', 'end_date', 'is_current', 'salary'
            ]),
            'graduation' => $candidate->educations->where('education_type', 'graduation')->first()?->only([
                'education_level', 'specialization', 'college_name', 'complete_years', 'complete_month'
            ]),
            'postGraduation' => $candidate->educations->where('education_type', 'post_graduation')->first()?->only([
                'education_level', 'specialization', 'college_name', 'complete_years', 'complete_month'
            ]),
        ]
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

       $email = $request->email;
    $password = $request->password;

    $candidate = Candidate::where('email', $email)->first();

    if (!$candidate || !Hash::check($password, $candidate->password)) {
        return response()->json([
            "success" => false,
            "message" => "Invalid email or password"
        ], 400);
    }

    // 🔥 Generate Sanctum token
    $token = $candidate->createToken('candidate-api')->plainTextToken;

    // 🔥 Store token in DB + update last login
    $candidate->update([
        'token' => $token,
        'last_login' => now(),
    ]);

    return response()->json([
        "success" => true,
        "message" => "User logged in successfully",
        "token" => $token,
        "user" => $candidate
    ]);


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
        'experience_years' => 'nullable',
        'experience_months' => 'nullable',
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
