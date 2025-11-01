<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\CandidateEducation;

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

    // === VALIDATION ===
    $validated = $request->validate([
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

        // Experience
        'experience_years'    => 'nullable',
        'experience_months'   => 'nullable',
        'experience_level'    => 'sometimes|in:Fresher,Experienced',
        'is_working'          => 'nullable',
        'notice_period'       => 'sometimes|string|nullable',
        'job_title'           => 'sometimes|string|nullable',
        'job_roles'           => 'sometimes|string|nullable',
        'company_name'        => 'sometimes|string|nullable',
        'current_salary'      => 'sometimes|numeric|nullable',
        'employment_type'     => 'sometimes|string|nullable',
        'experience_type'     => 'sometimes|string|nullable',

        // Education (Flat)
        'currently_pursuing'  => 'nullable',
        'highest_education'   => 'nullable',
        'complete_years'      => 'nullable',
        'complete_month'      => 'nullable',
        'school_medium'       => 'nullable',

        // === NESTED EDUCATION BLOCKS ===
        'graduation.education_level' => 'nullable|string',
        'graduation.specialization'  => 'nullable|string',
        'graduation.college_name'    => 'nullable|string',
        'graduation.complete_years'  => 'nullable|integer|min:1900|max:2100',
        'graduation.complete_month'  => 'nullable|string',
        'graduation.school_medium'   => 'nullable|string',

        'postGraduation.education_level' => 'nullable|string',
        'postGraduation.specialization'  => 'nullable|string',
        'postGraduation.college_name'    => 'nullable|string',
        'postGraduation.complete_years'  => 'nullable|integer|min:1900|max:2100',
        'postGraduation.complete_month'  => 'nullable|string',
        'postGraduation.school_medium'   => 'nullable|string',

        // Arrays (JSON)
        'skills'               => 'sometimes',
        'preferred_job_titles' => 'sometimes',
        'preferred_languages'  => 'sometimes',
        'preferred_locations'  => 'sometimes',

        // Files
        'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'resume'      => 'nullable|mimes:pdf|max:5120',

        // Password
        'password'    => 'sometimes|string|min:8',
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
        'complete_years'      => $request->complete_years,
        'complete_month'      => $request->complete_month,
        'school_medium'       => $request->school_medium,

        'experience_years'    => $request->experience_years,
        'experience_months'   => $request->experience_months,
        'experience_level'    => $request->experience_level,
        'is_working'          => $request->is_working,
        'notice_period'       => $request->notice_period,
        'job_title'           => $request->job_title,
        'job_roles'           => $request->job_roles,
        'company_name'        => $request->company_name,
        'current_salary'      => $request->current_salary,
        'employment_type'     => $request->employment_type,
        'experience_type'     => $request->experience_type,

        

        'prefers_night_shift' => $request->filled('prefers_night_shift') ? $request->prefers_night_shift : $candidate->prefers_night_shift,
        'prefers_day_shift'   => $request->filled('prefers_day_shift') ? $request->prefers_day_shift : $candidate->prefers_day_shift,
        'work_from_home'      => $request->filled('work_from_home') ? $request->work_from_home : $candidate->work_from_home,
        'work_from_office'    => $request->filled('work_from_office') ? $request->work_from_office : $candidate->work_from_office,

        'english_level'       => $request->english_level,
        'preferred_language'  => $request->preferred_language,

        // JSON Arrays
        'skills'              => $request->has('skills') ? json_encode($request->skills) : $candidate->skills,
        'preferred_job_titles'=> $request->has('preferred_job_titles') ? json_encode($request->preferred_job_titles) : $candidate->preferred_job_titles,
        'preferred_languages' => $request->has('preferred_languages') ? json_encode($request->preferred_languages) : $candidate->preferred_languages,
        'preferred_locations' => $request->has('preferred_locations') ? json_encode($request->preferred_locations) : $candidate->preferred_locations,
    ]);

    // Password
    if ($request->filled('password')) {
        $candidate->password = Hash::make($request->password);
    }

    $candidate->doneprofile = 1;

    $graduation = $request->input('graduation');


    $postGraduation = $request->input('postGraduation');

    if (is_string($graduation)) {
    $graduation = json_decode($graduation, true);
}
if (is_string($postGraduation)) {
    $postGraduation = json_decode($postGraduation, true);
}


$educationData = [
    'graduation' => $graduation ?? [],
    'postGraduation' => $postGraduation ?? [],
];
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

    // === SAVE EDUCATION BLOCKS (Graduation & Post-Graduation) ===
    $educationData = [
        'graduation' => $request->input('graduation', []),
        'postGraduation' => $request->input('postGraduation', []),
    ];

    \Log::info('Raw graduation:', [$request->input('graduation')]);
\Log::info('Raw postGraduation:', [$request->input('postGraduation')]);

   $educationData = [
    'graduation' => $graduation,
    'postGraduation' => $postGraduation,
];

foreach (['graduation', 'postGraduation'] as $type) {
    $data = $educationData[$type];

    if (!empty($data['education_level'])) {
        CandidateEducation::updateOrCreate(
            [
                'candidate_id' => $candidate->id,
                'education_type' => $type === 'graduation' ? 'graduation' : 'post_graduation',
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

    // === OPTIONAL: Sync flat education fields from Graduation ===
    if ($grad = $candidate->graduation) {
        $candidate->updateQuietly([
            'education_level' => $grad->education_level,
            'specialization'  => $grad->specialization,
            'college_name'    => $grad->college_name,
            'complete_years'  => $grad->complete_years,
            'complete_month'  => $grad->complete_month,
            'school_medium'   => $grad->school_medium,
        ]);
    }

    // === RESPONSE ===
    return response()->json([
        'success' => true,
        'message' => 'Candidate profile updated successfully',
        'data' => [
            'candidate' => $candidate->only([
                'id', 'full_name', 'email', 'highest_education', 'doneprofile'
            ]),
            'graduation' => $candidate->graduation ? $candidate->graduation->only([
                'education_level', 'specialization', 'college_name', 'complete_years', 'complete_month'
            ]) : null,
            'postGraduation' => $candidate->postGraduation ? $candidate->postGraduation->only([
                'education_level', 'specialization', 'college_name', 'complete_years', 'complete_month'
            ]) : null,
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
