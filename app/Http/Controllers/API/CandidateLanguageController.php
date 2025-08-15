<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CandidateLanguage;
use Illuminate\Http\Request;

class CandidateLanguageController extends Controller
{
    public function index()
    {
        return response()->json(CandidateLanguage::with('candidate')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'candidate_id' => 'required|exists:candidates,id',
            'language_name' => 'required|string|max:255',
        ]);

        $language = CandidateLanguage::create($validated);
        return response()->json($language, 201);
    }

    public function show($id)
    {
        $language = CandidateLanguage::with('candidate')->findOrFail($id);
        return response()->json($language);
    }

    public function update(Request $request, $id)
    {
        $language = CandidateLanguage::findOrFail($id);

        $validated = $request->validate([
            'candidate_id' => 'sometimes|exists:candidates,id',
            'language_name' => 'sometimes|string|max:255',
        ]);

        $language->update($validated);
        return response()->json($language);
    }

    public function destroy($id)
    {
        CandidateLanguage::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
