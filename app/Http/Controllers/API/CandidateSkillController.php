<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CandidateSkill;
use Illuminate\Http\Request;

class CandidateSkillController extends Controller
{
    public function index()
    {
        return response()->json(CandidateSkill::with('candidate')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'skill_name' => 'required|string|max:255',
        ]);

        $skill = CandidateSkill::create($validated);
        return response()->json($skill, 201);
    }

    public function show($id)
    {
        $skill = CandidateSkill::with('candidate')->findOrFail($id);
        return response()->json($skill);
    }

    public function update(Request $request, $id)
    {
        $skill = CandidateSkill::findOrFail($id);

        $validated = $request->validate([
            'candidate_id' => 'sometimes|exists:candidates,id',
            'skill_name' => 'sometimes|string|max:255',
        ]);

        $skill->update($validated);
        return response()->json($skill);
    }

    public function destroy($id)
    {
        CandidateSkill::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
