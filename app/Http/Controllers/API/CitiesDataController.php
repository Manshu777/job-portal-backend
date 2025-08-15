<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CandidateLanguage;
use Illuminate\Http\Request;


class CitiesDataController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:100',
            'tier' => 'required|integer',
            'is_live' => 'required|boolean',
            'centroid.latitude' => 'required|numeric',
            'centroid.longitude' => 'required|numeric',
            'childrens' => 'required|array',
            'childrens.*' => 'string',
        ]);

        $cityData = CitiesData::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'tier' => $validated['tier'],
            'is_live' => $validated['is_live'],
            'centroid_latitude' => $validated['centroid']['latitude'],
            'centroid_longitude' => $validated['centroid']['longitude'],
            'childrens' => $validated['childrens'],
        ]);

        return response()->json([
            'message' => 'City data created successfully',
            'data' => $cityData
        ], 201);
    }

    public function index(): JsonResponse
    {
        $cities = CitiesData::all();
        
        // Transform the data to match the input format
        $formattedCities = $cities->map(function ($city) {
            return [
                'name' => $city->name,
                'id' => $city->id,
                'type' => $city->type,
                'tier' => $city->tier,
                'is_live' => $city->is_live,
                'centroid' => [
                    'latitude' => $city->centroid_latitude,
                    'longitude' => $city->centroid_longitude,
                ],
                'childrens' => $city->childrens,
            ];
        });

        return response()->json([
            'tier_1_clusters' => $formattedCities->where('tier', 1)->values(),
            'other_cities' => $formattedCities->where('tier', '!=', 1)->values(),
        ]);
    }
}
