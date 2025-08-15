<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Courseeducations;
use Illuminate\Http\Request;

class CourseeducationController extends Controller
{
    public function index()
    {
        return response()->json(Courseeducations::all(), 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ordered_id' => 'required|integer',
            'title' => 'required|string|max:255',
            'verification_status' => 'required|string|in:verified,pending,unverified',
            'education_level' => 'required|integer',
            'cohort_id' => 'nullable|integer',
            'specializations' => 'nullable|array',
            'specializations.*.id' => 'required_with:specializations|integer',
            'specializations.*.title' => 'required_with:specializations|string',
            'specializations.*.verification_status' => 'required_with:specializations|string',
            'specializations.*.keywords' => 'required_with:specializations|array'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $course = Courseeducations::create($request->all());
        return response()->json($course, 201);
    }

    public function show($id)
    {
        $course = Courseeducations::findOrFail($id);
        return response()->json($course, 200);
    }
}