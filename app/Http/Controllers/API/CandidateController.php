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
use App\Models\CandidateEducation;
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


      public function getSuggestions(Request $request)
{
    // 1. Validate input
    $validator = \Validator::make($request->all(), [
        'query' => 'nullable|string|max:255',
        'type' => 'required|string|in:keywords,locations',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => 'Invalid input',
            'messages' => $validator->errors()
        ], 422);
    }

    $queryString = strtolower(trim($request->input('query', '')));
    $type = $request->input('type');
    $suggestions = [];

    // 2. Build Query
    $query = Candidate::query();

    if ($type === 'keywords') {
        // Fetch suggestions for keywords (job_title, preferred_job_titles, specialization, degree)
          // Fetch suggestions for keywords (skills, job_title, experience_level, preferred_language, specialization, degree, state)
        $skills = $query->select('skills as value')
            ->whereNotNull('skills')
            ->when($queryString, function ($q) use ($queryString) {
                $q->whereRaw('LOWER(skills) LIKE ?', ['%' . $queryString . '%']);
            })
            ->distinct()
            ->pluck('value')
            ->flatMap(function ($value) use ($queryString) {
                $skillsArray = is_string($value) ? json_decode($value, true) : $value;
                return is_array($skillsArray)
                    ? array_filter($skillsArray, function ($skill) use ($queryString) {
                        return $queryString ? stripos($skill, $queryString) !== false : true;
                    })
                    : [];
            })
            ->unique()
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            });

        $jobTitles = $query->select('job_title as value')
            ->whereNotNull('job_title')
            ->when($queryString, function ($q) use ($queryString) {
                $q->whereRaw('LOWER(job_title) LIKE ?', ['%' . $queryString . '%']);
            })
            ->distinct()
            ->pluck('value')
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            });

        $experienceLevels = $query->select('experience_level as value')
            ->whereNotNull('experience_level')
            ->when($queryString, function ($q) use ($queryString) {
                $q->whereRaw('LOWER(experience_level) LIKE ?', ['%' . $queryString . '%']);
            })
            ->distinct()
            ->pluck('value')
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            });

        $preferredLanguages = $query->select('preferred_languages')
            ->whereNotNull('preferred_languages')
            ->get()
            ->flatMap(function ($candidate) use ($queryString) {
                $languages = is_string($candidate->preferred_languages)
                    ? json_decode($candidate->preferred_languages, true)
                    : $candidate->preferred_languages;
                return is_array($languages)
                    ? array_filter($languages, function ($language) use ($queryString) {
                        return $queryString ? stripos($language, $queryString) !== false : true;
                    })
                    : [];
            })
            ->unique()
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            });

        $specializations = $query->select('specialization as value')
            ->whereNotNull('specialization')
            ->when($queryString, function ($q) use ($queryString) {
                $q->whereRaw('LOWER(specialization) LIKE ?', ['%' . $queryString . '%']);
            })
            ->distinct()
            ->pluck('value')
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            });

        $degrees = $query->select('degree as value')
            ->whereNotNull('degree')
            ->when($queryString, function ($q) use ($queryString) {
                $q->whereRaw('LOWER(degree) LIKE ?', ['%' . $queryString . '%']);
            })
            ->distinct()
            ->pluck('value')
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            });

        $states = $query->select('state as value')
            ->whereNotNull('state')
            ->when($queryString, function ($q) use ($queryString) {
                $q->whereRaw('LOWER(state) LIKE ?', ['%' . $queryString . '%']);
            })
            ->distinct()
            ->pluck('value')
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            });

        // Merge and limit to 10 suggestions
        $suggestions = $jobTitles
            ->merge($experienceLevels)
            ->merge($preferredLanguages)
            ->merge($degrees)
            ->merge($states)
            ->unique('value')
            ->values();
    } elseif ($type === 'locations') {
        // Fetch suggestions for locations (city, preferred_locations)
        $cities = $query->select('city as value')
            ->whereNotNull('city')
            ->when($queryString, function ($q) use ($queryString) {
                $q->whereRaw('LOWER(city) LIKE ?', ['%' . $queryString . '%']);
            })
            ->distinct()
            ->pluck('value')
            ->map(function ($value) {
                return ['value' => $value, 'label' => $value];
            });



        // Merge and limit to 3 suggestions
        $suggestions = $cities
            ->unique('value')
            ->values();
    }

    // 3. Return Response
    return response()->json([
        'data' => $suggestions
    ]);
}


      public function getDistinctValues(Request $request)
    {
        // 1. Initialize query
        $query = Candidate::query();

        // 2. Get distinct locations (city, state)
        $cities = $query->select('city')
            ->whereNotNull('city')
            ->distinct()
            ->pluck('city')
            ->map(function ($city) {
                return ['value' => $city];
            })->values();

        $states = $query->select('state')
            ->whereNotNull('state')
            ->distinct()
            ->pluck('state')
            ->map(function ($state) {
                return ['value' => $state];
            })->values();

        // Combine city and state for locations
        $locations = $cities->merge($states)->unique('value')->values();

        // 3. Get distinct keyword-related fields
        $skills = $query->select('skills.skill_name as value')
            ->join('skills', 'candidates.id', '=', 'skills.candidate_id')
            ->whereNotNull('skills.skill_name')
            ->distinct()
            ->pluck('value')
            ->map(function ($skill) {
                return ['value' => $skill];
            })->values();

        $jobTitles = $query->select('job_title')
            ->whereNotNull('job_title')
            ->distinct()
            ->pluck('job_title')
            ->map(function ($jobTitle) {
                return ['value' => $jobTitle];
            })->values();

        $preferredJobTitles = $query->select('preferred_job_titles')
            ->whereNotNull('preferred_job_titles')
            ->get()
            ->flatMap(function ($candidate) {
                $titles = is_string($candidate->preferred_job_titles) 
                    ? json_decode($candidate->preferred_job_titles, true)
                    : $candidate->preferred_job_titles;
                return is_array($titles) ? $titles : [];
            })
            ->unique()
            ->map(function ($title) {
                return ['value' => $title];
            })->values();

        $specializations = $query->select('specialization')
            ->whereNotNull('specialization')
            ->distinct()
            ->pluck('specialization')
            ->map(function ($specialization) {
                return ['value' => $specialization];
            })->values();

        $degrees = $query->select('degree')
            ->whereNotNull('degree')
            ->distinct()
            ->pluck('degree')
            ->map(function ($degree) {
                return ['value' => $degree];
            })->values();

        // 4. Return response
        return response()->json([
            'data' => [
                'locations' => $locations,
                'cities' => $cities,
                'states' => $states,
                'skills' => $skills,
                'job_titles' => $jobTitles,
                'preferred_job_titles' => $preferredJobTitles,
                'specializations' => $specializations,
                'degrees' => $degrees,
            ]
        ]);
    }



    private function maskEmail($email)
{
    if (!$email || !str_contains($email, '@')) {
        return 'xxxx@xxxx.com';
    }

    [$name, $domain] = explode('@', $email);

    $maskedName = substr($name, 0, 2) . str_repeat('*', max(0, strlen($name) - 2));
    $domainPart = explode('.', $domain)[0] ?? '';
    $maskedDomain = substr($domainPart, 0, 2) . str_repeat('*', max(0, strlen($domainPart) - 2));

    return $maskedName . '@' . $maskedDomain . '.com';
}

    public function filter(Request $request)
{
    // Parse comma-separated strings into arrays for multi-select fields
    $multiFields = ['city', 'language', 'specialization', 'degree'];
    foreach ($multiFields as $field) {
        if ($request->has($field) && is_string($request->input($field))) {
            $request->merge([$field => array_map('trim', explode(',', $request->input($field)))]);
        }
    }

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
        'english_level' => 'nullable|string|in:beginner,intermediate,fluent',
        'max_age'        => 'nullable|integer|min:0',
        'gender'         => 'nullable|string|in:Male,Female,Other',
        'degree'         => 'nullable|array',
        'degree.*'       => 'string',
        'specialization' => 'nullable|array',
        'specialization.*' => 'string',
        'language'       => 'nullable|array',
        'language.*'     => 'string',
        'department'     => 'nullable|string',
        'city'           => 'nullable|array',
        'city.*'         => 'string',
        'english_level'  => 'nullable|string|in:beginner,intermediate,fluent',
        'employment_type' => 'nullable|string',
        'shift_preference' => 'nullable|string|in:day,night',
        'experience_type' => 'nullable|string|in:fresher,experienced',
        'page'           => 'nullable|integer|min:1',
        'per_page'       => 'nullable|integer|min:1|max:100',
        'number_revealed' => 'nullable|string|in:1,0,last-15-days,last-30-days,last-90-days',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'error' => 'Invalid input',
            'messages' => $validator->errors()
        ], 422);
    }

    // 2. Build Query
    $query = Candidate::with(['educations', 'experiences'])->select('candidates.*');
    

    // Resume filter
    if ($request->filled('has_resume')) {
        if ($request->input('has_resume') == 1) {
            $query->whereNotNull('resume');
        } else {
            $query->whereNull('resume');
        }
    }

    // Number revealed filter
    if ($request->filled('number_revealed')) {
        $employer = Auth::guard('employer-api')->user();
        if ($employer) {
            $numberRevealed = $request->input('number_revealed');

            if ($numberRevealed === '1') {
                $query->whereHas('employerview', fn($q) => $q->where('employer_id', $employer->id)->where('number_revealed', 1));
            } elseif ($numberRevealed === '0') {
                $query->where(function ($q) use ($employer) {
                    $q->whereHas('employerview', fn($sq) => $sq->where('employer_id', $employer->id)->where('number_revealed', 0))
                      ->orWhereDoesntHave('employerview', fn($sq) => $sq->where('employer_id', $employer->id));
                });
            } else {
                $periods = ['last-15-days' => 15, 'last-30-days' => 30, 'last-90-days' => 90];
                $days = $periods[$numberRevealed] ?? null;
                if ($days) {
                    $dateThreshold = Carbon::now()->subDays($days);
                    $query->whereHas('employerview', fn($q) => $q->where('employer_id', $employer->id)
                        ->where('number_revealed', 1)
                        ->where('revealed_at', '>=', $dateThreshold));
                }
            }
        } else {
            $query->whereRaw('1 = 0');
        }
    }

    if ($englishFluency = $request->input('english_level')) {
    $query->whereRaw('LOWER(english_level) = ?', [strtolower($englishFluency)]);
}

    // Experience type
    if ($experienceType = $request->input('experience_type')) {
        $totalMonths = DB::raw('(experience_years * 12 + experience_months)');
        if ($experienceType === 'fresher') {
            $query->where($totalMonths, 0);
        } elseif ($experienceType === 'experienced') {
            $query->where($totalMonths, '>', 0);
        }
    }

    // Min/Max experience
    if ($minExperience = $request->input('min_experience')) {
        $query->whereRaw('(experience_years * 12 + experience_months) >= ?', [(int)$minExperience * 12]);
    }
    if ($maxExperience = $request->input('max_experience')) {
        $query->whereRaw('(experience_years * 12 + experience_months) <= ?', [(int)$maxExperience * 12]);
    }

    // Salary
    if ($request->filled('min_salary')) {
        $query->where('current_salary', '>=', (float)$request->input('min_salary'));
    }
    if ($request->filled('max_salary')) {
        $query->where('current_salary', '<=', (float)$request->input('max_salary'));
    }

    // Locations
    if ($locations = $request->input('locations')) {
        $locations = is_array($locations) ? $locations : [$locations];
        $query->whereIn(DB::raw('LOWER(city)'), array_map('strtolower', $locations));
    }

    // Must-have keywords

      if ($keywords = $request->input('must_have_keywords')) {
      $keywordArray = array_filter(array_map('trim', explode(',', $keywords)));

    foreach ($keywordArray as $keyword) {
        $like = '%' . strtolower($keyword) . '%';

        $query->where(function ($sq) use ($like) {
              $sq->whereHas('skills', fn($s) => $s->whereRaw('LOWER(skill_name) LIKE ?', [$like]))
               ->orWhereRaw('LOWER(job_title) LIKE ?', [$like])
               ->orWhereRaw('LOWER(job_roles) LIKE ?', [$like])
               ->orWhereRaw("JSON_SEARCH(LOWER(JSON_EXTRACT(preferred_job_titles, '$')), 'one', ?) IS NOT NULL", [$like]);
        });
    }
}
  
  
       
    

    // Exclude keywords
     // Exclude keywords - properly exclude candidates matching any term
if ($excludeKeywords = $request->input('exclude_keywords')) {
    $excludeTerms = array_filter(array_map('trim', explode(',', $excludeKeywords)));

    foreach ($excludeTerms as $term) {
        $term = trim($term);
        if ($term === '') continue;

        $like = '%' . strtolower($term) . '%';

        $query->where(function ($q) use ($like) {
            $q->whereDoesntHave('skills', function ($s) use ($like) {
                $s->whereRaw('LOWER(skill_name) LIKE ?', [$like]);
            })
            ->whereRaw('COALESCE(LOWER(job_title), "") NOT LIKE ?', [$like])
            ->whereRaw('COALESCE(LOWER(job_roles), "") NOT LIKE ?', [$like])
            ->whereRaw('COALESCE(LOWER(city), "") NOT LIKE ?', [$like])
            ->whereRaw("JSON_SEARCH(LOWER(COALESCE(JSON_EXTRACT(preferred_job_titles, '$'), '[]')), 'one', ?) IS NULL", [$like]);
        });
    }
}

    

    // === EDUCATION FILTERS USING CandidateEducation ===
    if ($degrees = $request->input('degree')) {
        $degrees = is_array($degrees) ? $degrees : [$degrees];
        if (!in_array('any', array_map('strtolower', $degrees), true)) {
            $lowerDegrees = array_map('strtolower', $degrees);
            $query->whereHas('educations', fn($q) => $q->whereIn(DB::raw('LOWER(education_level)'), $lowerDegrees));
        }
    }

    if ($specializations = $request->input('specialization')) {
        $specializations = is_array($specializations) ? $specializations : [$specializations];
        $query->whereHas('educations', function ($q) use ($specializations) {
            $q->where(function ($sq) use ($specializations) {
                foreach ($specializations as $spec) {
                    $spec = trim($spec);
                    if ($spec) {
                        $sq->orWhereRaw('LOWER(specialization) LIKE ?', ['%' . strtolower($spec) . '%']);
                    }
                }
            });
        });
    }

    if ($educationType = $request->input('education')) {
        $map = ['graduate' => 'graduation', 'post-graduate' => 'post_graduation', 'others' => 'other'];
        $type = $map[strtolower($educationType)] ?? null;
        if ($type) {
            $query->whereHas('educations', fn($q) => $q->where('education_type', $type));
        }
    }

    // Active user
    if ($request->filled('active')) {
        $query->where('active_user', $request->input('active'));
    }

    // Activity period
    if ($request->filled('activity_period')) {
        $periods = [
            '3-days' => now()->subDays(3),
            '7-days' => now()->subDays(7),
            '15-days' => now()->subDays(15),
            '1-month' => now()->subMonth(),
            '3-months' => now()->subMonths(3),
            '7-months' => now()->subMonths(7),
            '1-year' => now()->subYear(),
        ];
        $date = $periods[$request->input('activity_period')] ?? null;
        if ($date) {
            $query->where('last_login', '>=', $date);
        }
    }

    // Age
    $now = now();
    if ($minAge = $request->input('min_age')) {
        $query->where('dob', '<=', $now->copy()->subYears($minAge)->toDateString());
    }
    if ($maxAge = $request->input('max_age')) {
        $query->where('dob', '>', $now->copy()->subYears($maxAge + 1)->toDateString());
    }

    // Gender
    if ($gender = $request->input('gender')) {
        $query->where('gender', $gender);
    }

    // Language
    if ($languages = $request->input('language')) {
        $languages = is_array($languages) ? $languages : [$languages];
        $query->where(function ($q) use ($languages) {
            foreach ($languages as $lang) {
                $q->orWhereRaw('LOWER(preferred_language) LIKE ?', ['%' . strtolower(trim($lang)) . '%']);
            }
        });
    }

    // City
    if ($cities = $request->input('city')) {
        $cities = is_array($cities) ? $cities : [$cities];
        $query->whereIn(DB::raw('LOWER(city)'), array_map('strtolower', array_map('trim', $cities)));
    }

    // English level
    if ($englishFluency = $request->input('english_level')) {
        $query->whereRaw('LOWER(english_level) = ?', [strtolower($englishFluency)]);
    }

    // Department
    if ($department = $request->input('department')) {
        $query->whereRaw('LOWER(job_roles) LIKE ?', ['%' . strtolower($department) . '%']);
    }

    // Employment type
    if ($employmentType = $request->input('employment_type')) {
        $query->whereRaw('LOWER(employment_type) = ?', [strtolower($employmentType)]);
    }

    // Shift preference
    if ($shiftPrefs = $request->input('shift_preference')) {
        $query->where(function ($q) use ($shiftPrefs) {
            if ($shiftPrefs === 'day') $q->orWhere('prefers_day_shift', 1);
            if ($shiftPrefs === 'night') $q->orWhere('prefers_night_shift', 1);
        });
    }

    // Clone for faceting
    $facetBase = (clone $query);
    $query->orderBy('full_name', 'asc');

    // Pagination
    $candidates = $query->paginate($request->input('per_page', 10));

    // Mask phone, track visits
    $employer = Auth::guard('employer-api')->user();
    
    $candidates->getCollection()->transform(function ($candidate) use ($employer) {
    $numberRevealed = false;

    if ($employer) {
        $view = $candidate->employerview()
            ->where('employer_id', $employer->id)
            ->first();

        $numberRevealed = $view?->pivot->number_revealed ?? false;

        // Track profile visit
        if (!$view?->pivot->profile_visited ?? true) {
            $candidate->employerview()->syncWithoutDetaching([
                $employer->id => [
                    'profile_visited' => true,
                    'visited_at' => now(),
                ]
            ]);
        }

        if ($numberRevealed) {
            $candidate->number = $candidate->number;
            $candidate->email  = $candidate->email;
        } else {
            $candidate->number = 'XXXXXXXXXX';
            $candidate->email  = $this->maskEmail($candidate->email);
        }
    } else {
        $candidate->number = 'XXXXXXXXXX';
        $candidate->email  = $this->maskEmail($candidate->email);
    }

    // Add these flags
    $candidate->contact_revealed = $numberRevealed;
    $candidate->number_revealed  = $numberRevealed;
    $candidate->email_revealed   = $numberRevealed;

    // === THIS IS THE KEY PART: Attach related data ===
    $candidate->educations = $candidate->educations->map(function ($edu) {
        return [
            'education_level'   => $edu->education_level,
            'degree'            => $edu->degree,
            'specialization'    => $edu->specialization,
            'institute'         => $edu->institute,
            'year_of_passing'   => $edu->year_of_passing,
            'education_type'    => $edu->education_type,
            // add any other fields you want
        ];
    });

    $candidate->experiences = $candidate->experiences->map(function ($exp) {
        return [
            'company_name'      => $exp->company_name,
            'job_title'         => $exp->job_title,
            'department'        => $exp->department,
            'start_date'        => $exp->start_date,
            'end_date'          => $exp->end_date ?? 'Present',
            'currently_working' => $exp->currently_working,
            'job_description'   => $exp->job_description,
            'skills_used'       => $exp->skills_used,
            // add more as needed
        ];
    });

    // Optional: Unset the relationship objects to reduce payload size
    unset($candidate->educations_relation);
    unset($candidate->experiences_relation);

    return $candidate;
});
    // === FACET COUNTS ===
    $candidateIds = $facetBase->pluck('id');

    // Degree Facet
    $degreeCounts = CandidateEducation::query()
        ->selectRaw('LOWER(education_level) AS value, COUNT(DISTINCT candidate_id) AS count')
        ->whereIn('candidate_id', $candidateIds)
        ->whereNotNull('education_level')
        ->groupBy(DB::raw('LOWER(education_level)'))
        ->orderByDesc('count')
        ->get()
        ->map(fn($r) => ['value' => $r->value, 'count' => $r->count])
        ->values();

    // Specialization Facet
    $specializationCounts = CandidateEducation::query()
        ->selectRaw('LOWER(specialization) AS value, COUNT(DISTINCT candidate_id) AS count')
        ->whereIn('candidate_id', $candidateIds)
        ->whereNotNull('specialization')
        ->groupBy(DB::raw('LOWER(specialization)'))
        ->orderByDesc('count')
        ->get()
        ->map(fn($r) => ['value' => $r->value, 'count' => $r->count])
        ->values();

    // Other Facets (unchanged)
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
            if ($lang) $languageMap[$lang] = ($languageMap[$lang] ?? 0) + 1;
        }
    }
    $languageCounts = collect($languageMap)->map(fn($c, $v) => ['value' => $v, 'count' => $c])->values();

    $deptMap = [];
    foreach ((clone $facetBase)->pluck('job_roles') as $roles) {
        $decoded = is_string($roles) ? json_decode($roles, true) : $roles;
        if (is_array($decoded)) {
            foreach ($decoded as $role) {
                $role = strtolower(trim($role));
                if ($role) $deptMap[$role] = ($deptMap[$role] ?? 0) + 1;
            }
        }
    }
    $departmentCounts = collect($deptMap)->map(fn($c, $v) => ['value' => $v, 'count' => $c])->values();

    $englishFluencyCounts = (clone $facetBase)
        ->select('english_level as value', DB::raw('COUNT(*) as count'))
        ->whereNotNull('english_level')
        ->groupBy('english_level')
        ->get();

    $ages = (clone $facetBase)->selectRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) as age')->pluck('age');
    $minAge = $ages->min();
    $maxAge = $ages->max();

    // Return
    return response()->json([
        'data' => $candidates->items(),
        'pagination' => [
            'total' => $candidates->total(),
            'per_page' => $candidates->perPage(),
            'current_page' => $candidates->currentPage(),
            'last_page' => $candidates->lastPage(),
            'next_page_url' => $candidates->nextPageUrl(),
            'prev_page_url' => $candidates->previousPageUrl(),
        ],
        'filters' => [
            'degrees' => $degreeCounts,
            'specializations' => $specializationCounts,
            'languages' => $languageCounts,
            'departments' => $departmentCounts,
            'cities' => $cityCounts,
            'employment_types' => $employmentTypeCounts,
            'shift_preferences' => $shiftPrefsCounts,
            'english_levels' => $englishFluencyCounts,
            'min_age' => $minAge,
            'max_age' => $maxAge,
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

    // Optional: if candidate has no contact info at all
    if (!$candidate->number && !$candidate->email) {
        return response()->json(['error' => 'Candidate has no contact information'], 400);
    }

    // Check if already revealed (we only charge once)
    $existing = $candidate->employerview()
        ->where('employer_id', $employer->id)
        ->first();

    $alreadyRevealed = $existing?->pivot->number_revealed ?? false;

    if ($alreadyRevealed) {
        return response()->json([
            'message' => 'Contact already revealed',
            'number' => $candidate->number,
            'email'  => $candidate->email,
            'remaining_database_credits' => $employer->database_credits,
        ]);
    }

    // Deduct credit only ONCE
    $minimumCreditsRequired = 1;
    if (!$employer->hasEnoughCredits($minimumCreditsRequired, 'database')) {
        return response()->json([
            'error' => 'Insufficient database credits',
            'current_database_credits' => $employer->database_credits,
        ], 403);
    }

    // Deduct 1 credit
    $employer->deductCredits(1, 'database');

    // Reveal BOTH number and email by setting number_revealed = true
    $candidate->employerview()->syncWithoutDetaching([
        $employer->id => [
            'number_revealed' => true,
            'revealed_at'     => now(),
        ]
    ]);

    return response()->json([
        'message'                    => 'Contact revealed successfully (Phone + Email)',
        'number'                     => $candidate->number,
        'email'                      => $candidate->email,
        'remaining_database_credits' => $employer->fresh()->database_credits, // fresh() to get updated value
    ]);
}
  
   
}