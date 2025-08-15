<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CandidateExperience;
use Illuminate\Http\Request;

class CandidateExperienceController extends Controller
{
    /**
     * Display a listing of all candidate experiences.
     */
    public function index()
    {
        return response()->json(CandidateExperience::with('candidate')->get());
    }

    /**
     * Store a new candidate experience.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'job_title' => 'required|string|max:255',
            'job_roles' => 'nullable|array',
            'company_name' => 'required|string|max:255',
            'experience_years' => 'required|integer|min:0',
            'experience_months' => 'required|integer|min:0|max:11',
            'current_salary' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
        ]);

        $experience = CandidateExperience::create($validated);
        return response()->json($experience, 201);
    }

    /**
     * Show a specific candidate experience.
     */
    public function show($id)
    {
        $experience = CandidateExperience::with('candidate')->findOrFail($id);
        return response()->json($experience);
    }

    /**
     * Update a candidate experience.
     */
    public function update(Request $request, $id)
    {
        $experience = CandidateExperience::findOrFail($id);

        $validated = $request->validate([
            'candidate_id' => 'sometimes|exists:candidates,id',
            'job_title' => 'sometimes|string|max:255',
            'job_roles' => 'nullable|array',
            'company_name' => 'sometimes|string|max:255',
            'experience_years' => 'sometimes|integer|min:0',
            'experience_months' => 'sometimes|integer|min:0|max:11',
            'current_salary' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
        ]);

        $experience->update($validated);
        return response()->json($experience);
    }

    /**
     * Delete a candidate experience.
     */
    public function destroy($id)
    {
        CandidateExperience::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
