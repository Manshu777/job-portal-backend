<?php 

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\Qualification;
use App\Models\Specialization;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class QualificationsController extends Controller
{
    // Get all qualifications
    public function index(): JsonResponse
    {
        $qualifications = Qualification::all();
        return response()->json([
            'status' => 'success',
            'data' => $qualifications
        ], 200);
    }

    // Get single qualification by ID
    public function show($id): JsonResponse
    {
        $qualification = Qualification::find($id);
        
        if (!$qualification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Qualification not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $qualification
        ], 200);
    }

    // Create new qualification
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'verification_status' => 'required|string',
            'education_level' => 'required|integer',
            'aliases' => 'required|json',
            'cohort_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $qualification = Qualification::create($request->all());
        
        return response()->json([
            'status' => 'success',
            'data' => $qualification
        ], 201);
    }

     public function getByEducationLevel($level): JsonResponse
    {
        // Validate the education_level parameter
        $validator = Validator::make(['education_level' => $level], [
            'education_level' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        // Fetch qualifications with the specified education_level
        $qualifications = Qualification::where('education_level', $level)->get();

        if ($qualifications->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No qualifications found for the specified education level',
                'data' => []
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'data' => $qualifications
        ], 200);
    }

    // Update qualification
    public function update(Request $request, $id): JsonResponse
    {
        $qualification = Qualification::find($id);
        
        if (!$qualification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Qualification not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'verification_status' => 'string',
            'education_level' => 'integer',
            'aliases' => 'json',
            'cohort_id' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $qualification->update($request->all());
        
        return response()->json([
            'status' => 'success',
            'data' => $qualification
        ], 200);
    }

    // Delete qualification
    public function destroy($id): JsonResponse
    {
        $qualification = Qualification::find($id);
        
        if (!$qualification) {
            return response()->json([
                'status' => 'error',
                'message' => 'Qualification not found'
            ], 404);
        }

        $qualification->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Qualification deleted successfully'
        ], 200);
    }

    // Search qualifications by title or aliases
    public function search(Request $request): JsonResponse
    {
        $searchTerm = $request->query('term');
        
        if (!$searchTerm) {
            return response()->json([
                'status' => 'error',
                'message' => 'Search term is required'
            ], 422);
        }

        $qualifications = Qualification::where('title', 'like', '%' . $searchTerm . '%')
            ->orWhereRaw('JSON_CONTAINS(aliases, ?)', [json_encode($searchTerm)])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $qualifications
        ], 200);
    }

    // Get specializations by qualification ID
 public function specializations($qualificationId): JsonResponse
{
    $qualification = Qualification::find($qualificationId);

    if (!$qualification) {
        return response()->json([
            'status' => 'error',
            'message' => 'Qualification not found'
        ], 404);
    }

    $specializations = Specialization::where('course_id', $qualification->id)->get();

    return response()->json([
        'status' => 'success',
        'data' => [
            'qualification' => $qualification,
            'specializations' => $specializations,
        ],
    ], 200);
}

}