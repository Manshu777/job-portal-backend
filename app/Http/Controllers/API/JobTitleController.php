<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller; 
use App\Models\JobTitle;
use App\Models\JobResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class JobTitleController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'job_title' => 'required|string|max:255',
            'results' => 'required|array',
            'results.*.id' => 'required|string',
            'results.*.text' => 'required|string',
            'results.*.type' => 'required|string|in:sub_department,title',
            'results.*.is_generic' => 'nullable|boolean',
            'results.*.is_l2' => 'nullable|boolean',
            'results.*.similarity' => 'nullable|numeric|between:0,1',
            'results.*.embeddings' => 'nullable|json',
            'results.*.10th_or_below_10th' => 'nullable|boolean',
            'results.*.12th_pass' => 'nullable|boolean',
            'results.*.graduate' => 'nullable|boolean',
            'results.*.diploma' => 'nullable|boolean',
            'results.*.iti' => 'nullable|boolean',
            'results.*.post_graduate' => 'nullable|boolean',
            'results.*.source' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $jobTitle = JobTitle::create(['job_title' => $request->job_title]);

        foreach ($request->results as $result) {
            $jobTitle->results()->create([
                'result_id' => $result['id'],
                'text' => $result['text'],
                'type' => $result['type'],
                'is_generic' => $result['is_generic'] ?? null,
                'is_l2' => $result['is_l2'] ?? false,
                'similarity' => $result['similarity'] ?? null,
                'embeddings' => $result['embeddings'] ?? null,
                '10th_or_below_10th' => $result['10th_or_below_10th'] ?? null,
                '12th_pass' => $result['12th_pass'] ?? null,
                'graduate' => $result['graduate'] ?? null,
                'diploma' => $result['diploma'] ?? null,
                'iti' => $result['iti'] ?? null,
                'post_graduate' => $result['post_graduate'] ?? null,
                'source' => $result['source'] ?? null,
            ]);
        }

        return response()->json(['message' => 'Job title and results created successfully', 'data' => $jobTitle->load('results')], 201);
    }

    public function index()
    {
        $jobTitles = JobTitle::with('results')->get();
        return response()->json(['data' => $jobTitles], 200);
    }

    public function show($id)
    {
        $jobTitle = JobTitle::with('results')->findOrFail($id);
        return response()->json(['data' => $jobTitle], 200);
    }
     public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $query = $request->input('query');
        $jobTitles = JobTitle::with('results')
            ->where('job_title', 'LIKE', "%{$query}%")
            ->get();

        if ($jobTitles->isEmpty()) {
            return response()->json(['message' => 'No job titles found matching the query'], 404);
        }

        return response()->json(['data' => $jobTitles], 200);
    }
}
