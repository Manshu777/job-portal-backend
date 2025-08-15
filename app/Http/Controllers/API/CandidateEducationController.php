<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CandidateEducation;
use Illuminate\Http\Request;

class CandidateEducationController extends Controller
{
    /**
     * Display a listing of the candidate educations.
     */
    public function index()
    {
        return response()->json(CandidateEducation::with('candidate')->get());
    }

    /**
     * Store a newly created education record.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'degree' => 'required|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'college_name' => 'required|string|max:255',
            'passing_marks' => 'nullable|string|max:255',
            'pursuing' => 'required|boolean',
        ]);

        $education = CandidateEducation::create($validated);
        return response()->json($education, 201);
    }

    /**
     * Display the specified education.
     */
    public function show($id)
    {
        $education = CandidateEducation::with('candidate')->findOrFail($id);
        return response()->json($education);
    }

    /**
     * Update the specified education.
     */
    public function update(Request $request, $id)
    {
        $education = CandidateEducation::findOrFail($id);

        $validated = $request->validate([
            'candidate_id' => 'sometimes|exists:candidates,id',
            'degree' => 'sometimes|string|max:255',
            'specialization' => 'nullable|string|max:255',
            'college_name' => 'sometimes|string|max:255',
            'passing_marks' => 'nullable|string|max:255',
            'pursuing' => 'sometimes|boolean',
        ]);

        $education->update($validated);
        return response()->json($education);
    }

    /**
     * Remove the specified education.
     */
    public function destroy($id)
    {
        CandidateEducation::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
