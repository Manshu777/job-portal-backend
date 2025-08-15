<?php

namespace App\Http\Controllers\API;

use App\Models\Candidate;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
class CandidateController extends Controller
{
    public function index()
    {
        $candidates = Candidate::with(['educations', 'experiences', 'skills', 'languages'])->get();
        return response()->json($candidates, 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'dob' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'email' => 'nullable|email|unique:candidates,email',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'prefers_night_shift' => 'nullable|boolean',
            'prefers_day_shift' => 'nullable|boolean',
            'work_from_home' => 'nullable|boolean',
            'work_from_office' => 'nullable|boolean',
            'field_job' => 'nullable|boolean',
            'employment_type' => 'nullable|string',
            'resume' => 'nullable|string',
            'active_user' => 'nullable|boolean',
            'last_login' => 'nullable|datetime',
            'total_jobs_applied' => 'nullable|integer',
            'total_job_views' => 'nullable|integer',
            'otp' => 'nullable|string',
            'otp_expires_at' => 'nullable|datetime',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $candidate = Candidate::create($validator->validated());

        return response()->json($candidate, 201);
    }

    public function show($id)
    {
        $candidate = Candidate::with(['educations', 'experiences', 'skills', 'languages'])->find($id);

        if (!$candidate) {
            return response()->json(['message' => 'Candidate not found'], 404);
        }

        return response()->json($candidate, 200);
    }

    public function update(Request $request, $id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json(['message' => 'Candidate not found'], 404);
        }

        $candidate->update($request->only([
            'full_name',
            'dob',
            'gender',
            'email',
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
            'active_user',
            'last_login',
            'total_jobs_applied',
            'total_job_views',
            'otp',
            'otp_expires_at'
        ]));

        return response()->json($candidate, 200);
    }

    public function destroy($id)
    {
        $candidate = Candidate::find($id);

        if (!$candidate) {
            return response()->json(['message' => 'Candidate not found'], 404);
        }

        $candidate->delete();

        return response()->json(['message' => 'Candidate deleted successfully'], 200);
    }



    public function filter(Request $request)
    {
        // 1. Validation
        $validator = Validator::make($request->all(), [
            'min_experience' => 'nullable|integer|min:0',
            'max_experience' => 'nullable|integer|min:0',
            'min_salary'     => 'nullable|numeric|min:0',
            'max_salary'     => 'nullable|numeric|min:0',
            'locations'      => 'nullable|array',
            'locations.*'    => 'string',
            'education'      => 'nullable|string|in:graduate,post-graduate,others',
            'activity_period' => 'nullable|string|in:3-days,7-days,15-days,1-month,3-months,7-months,1-year',
            'has_resume'     => 'nullable|boolean',
            'must_have_keywords' => 'nullable|string',
            'exclude_keywords'  => 'nullable|string',
            'active'         => 'nullable|in:1,0',
            
            'min_age'        => 'nullable|integer|min:0',
            'max_age'        => 'nullable|integer|min:0',
            'gender'         => 'nullable|string|in:Male,Female,Other',
            'degree'         => 'nullable|string',
            'specialization' => 'nullable|string',
            'language'       => 'nullable|string',
            'department'     => 'nullable|string',
            'city'           => 'nullable|string',
            'english_fluency' => 'nullable|string|in:beginner,intermediate,fluent',
            'employment_type' => 'nullable|string',
            'shift_preference' => 'nullable|string|in:day,night',
            'page'           => 'nullable|integer|min:1',
            'per_page'       => 'nullable|integer|min:1|max:100',
            'number_revealed' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid input',
                'messages' => $validator->errors()
            ], 422);
        }

        // 2. Build Query
        $query = Candidate::query();

        if ($request->filled('has_resume')) {
            if ($request->input('has_resume') == 1) {
                $query->whereNotNull('resume');
            } else {
                $query->whereNull('resume');
            }
        }


        if ($request->filled('number_revealed')) {
        $employer = Auth::guard('employer-api')->user();
        if ($employer) {
            $query->whereHas('employerViews', function ($q) use ($employer, $request) {
                $q->where('employer_id', $employer->id)
                  ->where('number_revealed', $request->input('number_revealed'));
            });
        } else {
            // If no employer is authenticated, return no results for number_revealed filter
            $query->whereRaw('1 = 0');
        }
    }
    

        if ($minExperience = $request->input('min_experience')) {
            $query->whereRaw('(experience_years * 12 + experience_months) >= ?', [(int)$minExperience * 12]);
        }

        if ($maxExperience = $request->input('max_experience')) {
            $query->whereRaw('(experience_years * 12 + experience_months) <= ?', [(int)$maxExperience * 12]);
        }

        if ($request->filled('min_salary')) {
            $query->where('current_salary', '>=', (float)$request->input('min_salary'));
        }

        if ($request->filled('max_salary')) {
            $query->where('current_salary', '<=', (float)$request->input('max_salary'));
        }

        if ($locations = $request->input('locations')) {
            if (is_array($locations)) {
                $query->whereIn(DB::raw('LOWER(city)'), array_map('strtolower', $locations));
            } else {
                $query->whereRaw('LOWER(city) = ?', [strtolower($locations)]);
            }
        }

        if ($keywords = $request->input('must_have_keywords')) {
            $keywordArray = array_map('trim', explode(',', $keywords));
            foreach ($keywordArray as $keyword) {
                $query->whereHas('skills', function ($q) use ($keyword) {
                    $q->whereRaw('LOWER(skill_name) LIKE ?', ['%' . strtolower($keyword) . '%']);
                });
            }
        }

        if ($excludeKeywords = $request->input('exclude_keywords')) {
            $excludeArray = array_map('trim', explode(',', $excludeKeywords));
            $query->whereDoesntHave('skills', function ($q) use ($excludeArray) {
                $q->whereIn(DB::raw('LOWER(skill_name)'), array_map('strtolower', $excludeArray));
            });
        }

        if ($request->filled('active')) {
            $query->where('active_user', $request->input('active'));
        }

        if ($request->filled('activity_period')) {
            $periods = [
                '3-days' => Carbon::now()->subDays(3),
                '7-days' => Carbon::now()->subDays(7),
                '15-days' => Carbon::now()->subDays(15),
                '1-month' => Carbon::now()->subMonth(),
                '3-months' => Carbon::now()->subMonths(3),
                '7-months' => Carbon::now()->subMonths(7),
                '1-year' => Carbon::now()->subYear(),
            ];
            if (isset($periods[$request->input('activity_period')])) {
                $query->where('last_login', '>=', $periods[$request->input('activity_period')]);
            }
        }

        // Age Filter
        $now = Carbon::now();
        if ($minAge = $request->input('min_age')) {
            $maxDob = $now->copy()->subYears($minAge)->toDateString();
            $query->where('dob', '<=', $maxDob);
        }
        if ($maxAge = $request->input('max_age')) {
            $minDob = $now->copy()->subYears($maxAge + 1)->toDateString();
            $query->where('dob', '>', $minDob);
        }

        if ($gender = $request->input('gender')) {
            $query->where('gender', $gender);
        }

        if ($degree = $request->input('degree')) {
            $query->whereRaw('LOWER(degree) = ?', [strtolower($degree)]);
        }

        if ($specialization = $request->input('specialization')) {
            $query->whereRaw('LOWER(specialization) LIKE ?', ['%' . strtolower($specialization) . '%']);
        }

        if ($language = $request->input('language')) {
            $query->whereRaw('LOWER(preferred_language) LIKE ?', ['%' . strtolower($language) . '%']);
        }

        if ($city = $request->input('city')) {
            $query->whereRaw('LOWER(city) LIKE ?', ['%' . strtolower($city) . '%']);
        }

        if ($englishFluency = $request->input('english_fluency')) {
            $query->whereRaw('LOWER(english_fluency) = ?', [strtolower($englishFluency)]);
        }

        if ($department = $request->input('department')) {
            $query->whereRaw('LOWER(job_roles) LIKE ?', ['%' . strtolower($department) . '%']);
        }

        if ($employmentType = $request->input('employment_type')) {
            $query->whereRaw('LOWER(employment_type) = ?', [strtolower($employmentType)]);
        }

        if ($shiftPrefs = $request->input('shift_preference')) {
            $query->where(function ($q) use ($shiftPrefs) {
                if ($shiftPrefs === 'day') {
                    $q->orWhere('prefers_day_shift', 1);
                }
                if ($shiftPrefs === 'night') {
                    $q->orWhere('prefers_night_shift', 1);
                }
            });
        }

        // Clone before ordering to avoid MySQL errors
        $facetBase = (clone $query);
        $query->orderBy('full_name', 'asc');

        // 3. Pagination
        $candidates = $query->paginate($request->input('per_page', 10));

        // 4. Modify Candidate Data to Mask Phone Numbers, Add Reveal and Visit Status
        $employer = Auth::guard('employer-api')->user();
        $candidates->getCollection()->transform(function ($candidate) use ($employer) {
            $hasRevealed = false;
            $profileVisited = false;
            if ($employer) {
                // Check if profile has been visited or number revealed
                $view = $candidate->employerViews()
                    ->where('employer_id', $employer->id)
                    ->first();

                $hasRevealed = $view && $view->pivot->number_revealed;
                $profileVisited = $view && $view->pivot->profile_visited;

                // Record profile visit if not already visited
                if (!$profileVisited) {
                    $candidate->employerViews()->syncWithoutDetaching([
                        $employer->id => [
                            'profile_visited' => true,
                            'visited_at' => now(),
                            'number_revealed' => $view ? $view->pivot->number_revealed : false,
                        ]
                    ]);
                    $profileVisited = true; // Update for response
                }

                $candidate->number = $hasRevealed ? $candidate->number : 'xxxxxxx';
            } else {
                $candidate->number = 'xxxxxxx';
            }
            $candidate->number_revealed = $hasRevealed;
            $candidate->profile_visited = $profileVisited;
            return $candidate;
        });

        // 5. Faceted Filters
        $degreeCounts = (clone $facetBase)
            ->select('degree as value', DB::raw('COUNT(*) as count'))
            ->whereNotNull('degree')
            ->groupBy('degree')
            ->get();

        $specializationCounts = (clone $facetBase)
            ->select('specialization as value', DB::raw('COUNT(*) as count'))
            ->whereNotNull('specialization')
            ->groupBy('specialization')
            ->get();

        $employmentTypeCounts = (clone $facetBase)
            ->select('employment_type as value', DB::raw('COUNT(*) as count'))
            ->whereNotNull('employment_type')
            ->groupBy('employment_type')
            ->get();

        $cityCounts = (clone $facetBase)
            ->select('city as value', DB::raw('COUNT(*) as count'))
            ->whereNotNull('city')
            ->groupBy('city')
            ->get();

        $shiftPrefsCounts = [
            ['value' => 'day', 'count' => (clone $facetBase)->where('prefers_day_shift', 1)->count()],
            ['value' => 'night', 'count' => (clone $facetBase)->where('prefers_night_shift', 1)->count()],
        ];

        $languageMap = [];
        foreach ((clone $facetBase)->pluck('preferred_language') as $langs) {
            foreach (explode(',', $langs) as $lang) {
                $lang = strtolower(trim($lang));
                if ($lang) {
                    $languageMap[$lang] = ($languageMap[$lang] ?? 0) + 1;
                }
            }
        }
        $languageCounts = collect($languageMap)->map(function ($count, $value) {
            return ['value' => $value, 'count' => $count];
        })->values();

        $deptMap = [];
        foreach ((clone $facetBase)->pluck('job_roles') as $roles) {
            $decoded = is_string($roles) ? json_decode($roles, true) : $roles;
            if (is_array($decoded)) {
                foreach ($decoded as $role) {
                    $role = strtolower(trim($role));
                    if ($role) {
                        $deptMap[$role] = ($deptMap[$role] ?? 0) + 1;
                    }
                }
            }
        }
        $departmentCounts = collect($deptMap)->map(function ($count, $value) {
            return ['value' => $value, 'count' => $count];
        })->values();

        $ages = (clone $facetBase)
            ->selectRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age')
            ->pluck('age');
        $minAge = $ages->min();
        $maxAge = $ages->max();

        // 6. Return Response
        return response()->json([
            'data' => $candidates->items(),
            'pagination' => [
                'total'         => $candidates->total(),
                'per_page'      => $candidates->perPage(),
                'current_page'  => $candidates->currentPage(),
                'last_page'     => $candidates->lastPage(),
                'next_page_url' => $candidates->nextPageUrl(),
                'prev_page_url' => $candidates->previousPageUrl(),
            ],
            'filters' => [
                'degrees'           => $degreeCounts,
                'specializations'   => $specializationCounts,
                'languages'         => $languageCounts,
                'departments'       => $departmentCounts,
                'cities'            => $cityCounts,
                'employment_types'  => $employmentTypeCounts,
                'shift_preferences' => $shiftPrefsCounts,
                'min_age'           => $minAge,
                'max_age'           => $maxAge,
            ]
        ]);
    }

    public function revealNumber(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'candidate_id' => 'required|integer|exists:candidates,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid input',
                'messages' => $validator->errors()
            ], 422);
        }

        $employer = Auth::guard('employer-api')->user();
        if (!$employer) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $candidate = Candidate::findOrFail($request->input('candidate_id'));

        if (!$candidate->number) {
            return response()->json(['error' => 'Candidate has no phone number'], 400);
        }

        $hasRevealed = $candidate->employerViews()
            ->where('employer_id', $employer->id)
            ->where('number_revealed', true)
            ->exists();

        if ($hasRevealed) {
            return response()->json([
                'message' => 'Number already revealed',
                'number' => $candidate->number
            ]);
        }

        try {
            $employer->deductCredits(1);
            $candidate->employerViews()->syncWithoutDetaching([
                $employer->id => ['number_revealed' => true, 'revealed_at' => now()]
            ]);
            return response()->json([
                'message' => 'Number revealed successfully',
                'number' => $candidate->number
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
  
   
}