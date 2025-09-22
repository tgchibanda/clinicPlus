<?php

namespace App\Http\Controllers;

use App\Models\Location;

class LocationController extends Controller
{
    /**
     * GET /api/locations
     * Return all locations for booking UI.
     */
    public function index()
    {
        $locations = Location::query()
            ->orderBy('name')
            ->get(['id', 'name', 'address']);

        return response()->json([
            'success' => true,
            'message' => 'Locations retrieved successfully.',
            'data'    => $locations,
        ], 200);
    }
}
