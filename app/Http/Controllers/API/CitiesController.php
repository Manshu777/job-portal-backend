<?php 

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;

class CitiesController extends Controller
{
    // Get all cities
    public function index(): JsonResponse
    {
        $cities = City::all();
        return response()->json([
            'status' => 'success',
            'data' => $cities
        ], 200);
    }

    // Get single city by ID
    public function show($id): JsonResponse
    {
        $city = City::find($id);
        
        if (!$city) {
            return response()->json([
                'status' => 'error',
                'message' => 'City not foufffnd'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $city
        ], 200);
    }

    // Create new city
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'city_id' => 'required|string|size:24|unique:cities',
            'name' => 'required|string|max:100',
            'status' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $city = City::create($request->all());
        
        return response()->json([
            'status' => 'success',
            'data' => $city
        ], 201);
    }

    // Update city
    public function update(Request $request, $id): JsonResponse
    {
        $city = City::find($id);
        
        if (!$city) {
            return response()->json([
                'status' => 'error',
                'message' => 'City not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'city_id' => 'string|size:24|unique:cities,city_id,'.$id,
            'name' => 'string|max:100',
            'status' => 'integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()
            ], 422);
        }

        $city->update($request->all());
        
        return response()->json([
            'status' => 'success',
            'data' => $city
        ], 200);
    }

    // Delete city
    public function destroy($id): JsonResponse
    {
        $city = City::find($id);
        
        if (!$city) {
            return response()->json([
                'status' => 'error',
                'message' => 'City not found'
            ], 404);
        }

        $city->delete();
        
        return response()->json([
            'status' => 'success',
            'message' => 'City deleted successfully'
        ], 200);
    }

    public function searchLocations($cityId): JsonResponse
    {
        $city = City::where('city_id', $cityId)->first();
        
        if (!$city) {
            return response()->json([
                'status' => 'error',
                'message' => 'City not foundffff'
            ], 404);
        }

        $locations = Location::where('city_id', $cityId)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'city' => $city,
                'locations' => $locations
            ]
        ], 200);
    }

    // Search cities by name
   public function search(Request $request): JsonResponse
{
    $searchTerm = $request->query('term');
    
    if (!$searchTerm) {
        return response()->json([
            'status' => 'error',
            'message' => 'Search term is required'
        ], 422);
    }

    $cities = City::where('name', 'like', '%' . $searchTerm . '%')->get();

    return response()->json([
        'status' => 'success',
        'data' => $cities
    ], 200);
}
}