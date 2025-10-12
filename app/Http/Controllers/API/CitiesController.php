<?php 

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\City;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

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



     public function searchCities(Request $request)
    {
        $query = $request->input('query');

        if (!$query) {
            return response()->json(['status' => 'error', 'message' => 'Please provide a search query'], 400);
        }

        $apiKey = env('GOOGLE_MAPS_API_KEY');

        $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
            'input' => $query,
            'key' => $apiKey,
            'types' => '(cities)',
            'components' => 'country:in',
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $response->json()['predictions'] ?? [],
        ]);
    }

    // 2️⃣ Search areas/localities inside a selected city

    public function searchAreas(Request $request)
{
    $cityName = $request->input('city');
    $areaQuery = $request->input('query'); // User's search term for the area (e.g., "Sector 17")

    // Validate inputs
    if (!$cityName || !$areaQuery) {
        return response()->json([
            'status' => 'error',
            'message' => 'Please provide both a city name and an area query'
        ], 400);
    }

    $apiKey = env('GOOGLE_MAPS_API_KEY');

    // Step 1: Get the place_id and location of the city
    $cityResponse = Http::get('https://maps.googleapis.com/maps/api/place/findplacefromtext/json', [
        'input' => $cityName,
        'inputtype' => 'textquery',
        'fields' => 'place_id,geometry',
        'key' => $apiKey,
    ]);

    $cityData = $cityResponse->json();

    if (empty($cityData['candidates'])) {
        return response()->json([
            'status' => 'error',
            'message' => 'City not found'
        ], 404);
    }

    $placeId = $cityData['candidates'][0]['place_id'];
    $cityLocation = $cityData['candidates'][0]['geometry']['location'];
    $lat = $cityLocation['lat'];
    $lng = $cityLocation['lng'];

    // Step 2: Search for areas within the city using Place Autocomplete API
    $response = Http::get('https://maps.googleapis.com/maps/api/place/autocomplete/json', [
        'input' => "$areaQuery, $cityName", // Combine area query and city name for context
        'key' => $apiKey,
        'types' => 'sublocality|neighborhood|locality', // Include relevant area types
        'location' => "$lat,$lng", // Bias results to the city's location
        'radius' => 20000, // Restrict to 20km around the city center
        'components' => 'country:in', // Restrict to India
    ]);

    $areas = $response->json();

    return response()->json([
        'status' => 'success',
        'city' => $cityName,
        'areas' => $areas['predictions'] ?? [],
    ]);
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