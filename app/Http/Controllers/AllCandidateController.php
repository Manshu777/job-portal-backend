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
    $candidate = Auth::guard('candidate-api')->user();

    if (!$candidate) {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized or candidate not found'
        ], 401);
    }

    // === VALIDATION ===
    $request->validate([
        'full_name'           => 'sometimes|string|max:255',
        'number'              => 'sometimes|string|max:20',
        'dob'                 => 'sometimes|date',
        'gender'              => 'sometimes|in:Male,Female,Other',
        'email'               => 'sometimes|email|max:255',
        'city'                => 'sometimes|string|nullable',
        'state'               => 'sometimes|string|nullable',

        'highest_education'   => 'nullable|string',
        'english_level'       => 'sometimes|string|nullable',

        'experience_years'    => 'nullable|integer|min:0|max:50',
        'experience_months'   => 'nullable|integer|min:0|max:11',
        'experience_level'    => 'sometimes|in:Fresher,Experienced,Both',
        'notice_period'       => 'sometimes|string|nullable',
        'current_salary'      => 'sometimes|numeric|nullable',

        'immediate_joiner'     => 'sometimes|boolean',
        'open_to_opportunities'=> 'sometimes|boolean',
        'prefers_day_shift'    => 'sometimes|boolean',
        'prefers_night_shift'  => 'sometimes|boolean',
        'work_from_home'       => 'sometimes|boolean',
        'work_from_office'     => 'sometimes|boolean',
        'field_job'            => 'sometimes|boolean',

        'graduation'           => 'sometimes|json',
        'postGraduation'       => 'sometimes|json',

        // Accept both JSON string and array
        'skills'               => 'nullable',
        'job_roles'            => 'nullable',
        'preferred_job_titles' => 'nullable',
        'preferred_languages'  => 'nullable',
        'preferred_locations'  => 'nullable',

        'profile_pic' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'resume'      => 'nullable|mimes:pdf|max:5120',
        'password'    => 'sometimes|string|min:8',
    ]);

    // === SAFELY HANDLE JSON ARRAY FIELDS ===
    $jsonFields = [
        'skills',
        'job_roles',
        'preferred_job_titles',
        'preferred_languages',
        'preferred_locations'
    ];

    $updateData = [];

    foreach ($jsonFields as $field) {
        if ($request->has($field)) {
            $value = $request->input($field);

            // If it's a JSON string like "[\"PHP\",\"Laravel\"]"
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $updateData[$field] = $decoded;
                    continue;
                }
            }

            // If it's already an array from FormData
            if (is_array($value)) {
                $updateData[$field] = array_values(array_filter($value));
                continue;
            }
        }
        // If not sent, keep old value (thanks to $casts, it stays array)
    }

    // === UPDATE CANDIDATE ===
    $candidate->update(array_merge([
        'full_name'           => $request->full_name,
        'number'              => $request->number,
        'dob'                 => $request->dob,
        'gender'              => $request->gender,
        'email'               => $request->email,
        'city'                => $request->city,
        'state'               => $request->state,
        'highest_education'   => $request->highest_education,
        'english_level'       => $request->english_level,

        'experience_years'    => $request->experience_years,
        'experience_months'   => $request->experience_months,
        'experience_level'    => $request->experience_level,
        'notice_period'       => $request->notice_period,
        'current_salary'      => $request->current_salary,

        'immediate_joiner'     => $request->boolean('immediate_joiner', $candidate->immediate_joiner),
        'open_to_opportunities'=> $request->boolean('open_to_opportunities', $candidate->open_to_opportunities),
        'prefers_day_shift'    => $request->boolean('prefers_day_shift', $candidate->prefers_day_shift),
        'prefers_night_shift'  => $request->boolean('prefers_night_shift', $candidate->prefers_night_shift),
        'work_from_home'       => $request->boolean('work_from_home', $candidate->work_from_home),
        'work_from_office'     => $request->boolean('work_from_office', $candidate->work_from_office),
        'field_job'            => $request->boolean('field_job', $candidate->field_job),
    ], $updateData));

    // === PASSWORD UPDATE ===
    if ($request->filled('password')) {
        $candidate->password = Hash::make($request->password);
    }

    // === PROFILE PICTURE ===
    if ($request->hasFile('profile_pic')) {
        if ($candidate->profile_pic) {
            Storage::disk('public')->delete($candidate->profile_pic);
        }
        $candidate->profile_pic = $request->file('profile_pic')->store('images', 'public');
    }

    // === RESUME ===
    if ($request->hasFile('resume')) {
        if ($candidate->resume) {
            Storage::disk('public')->delete($candidate->resume);
        }
        $candidate->resume = $request->file('resume')->store('pdf', 'public');
    }

    $candidate->doneprofile = 1;
    $candidate->save();

    // === EDUCATION BLOCKS ===
    foreach (['graduation', 'postGraduation'] as $type) {
        $data = $request->input($type);
        if (!$data) continue;

        if (is_string($data)) {
            $data = json_decode($data, true) ?? [];
        }

        if (!empty($data['education_level'])) {
            $educationType = $type === 'graduation' ? 'graduation' : 'post_graduation';

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
                    'complete_month'   => $data['complete_month'] ?? null,
                ]
            );
        }
    }

    // === EXPERIENCES (if any) ===
    if ($request->has('experiences')) {
        $raw = $request->input('experiences', []);
        $experiences = collect($raw)->map(fn($item) => is_string($item) ? json_decode($item, true) : $item)->filter()->values();

        if ($experiences->isNotEmpty()) {
            $candidate->experiences()->delete();
            $currentJob = null;

            foreach ($experiences as $exp) {
                if (!is_array($exp)) continue;

                $experience = $candidate->experiences()->create([
                    'job_title'    => $exp['job_title'] ?? null,
                    'company_name' => $exp['company_name'] ?? null,
                    'start_date'   => $exp['start_date'] ?? null,
                    'end_date'     => $exp['is_current'] ?? false ? null : ($exp['end_date'] ?? null),
                    'is_current'   => $exp['is_current'] ?? false,
                    'salary'       => $exp['salary'] ?? null,
                ]);

                if ($exp['is_current'] ?? false) {
                    $currentJob = $experience;
                }
            }

            if ($currentJob) {
                $candidate->update([
                    'job_title'      => $currentJob->job_title,
                    'company_name'   => $currentJob->company_name,
                    'current_salary' => $currentJob->salary,
                    'is_working'     => 'Yes',
                ]);
            } else {
                $candidate->update([
                    'job_title'      => null,
                    'company_name'   => null,
                    'current_salary' => null,
                    'is_working'     => 'No',
                ]);
            }
        }
    }

    // === FINAL RESPONSE ===
    return response()->json([
        'success' => true,
        'message' => 'Profile updated successfully',
        'data' => $candidate->refresh()->makeHidden(['password'])
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
