<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CinemaController extends Controller
{
    public function nearby(Request $request)
    {
        $request->validate([
            'lat' => 'required|numeric',
            'lon' => 'required|numeric',
        ]);

        $lat = $request->lat;
        $lon = $request->lon;
        $radius = 10000; // 10km

        $query = "[out:json];node[\"amenity\"=\"cinema\"](around:{$radius},{$lat},{$lon});out body;";

        try {
            $response = Http::timeout(10)->get('https://overpass-api.de/api/interpreter', [
                'data' => $query
            ]);

            if (!$response->successful()) {
                return response()->json(['success' => false, 'message' => 'Could not reach cinema data service.'], 500);
            }

            $data = $response->json();
            $elements = $data['elements'] ?? [];

            $cinemas = collect($elements)->map(function ($el) use ($lat, $lon) {
                $name = $el['tags']['name'] ?? 'Unknown Cinema';
                $cinLat = $el['lat'];
                $cinLon = $el['lon'];

                // Calculate distance in km
                $distance = $this->haversine($lat, $lon, $cinLat, $cinLon);

                return [
                    'name'     => $name,
                    'lat'      => $cinLat,
                    'lon'      => $cinLon,
                    'distance' => round($distance, 1),
                    'address'  => $el['tags']['addr:full'] ?? $el['tags']['addr:street'] ?? null,
                    'website'  => $el['tags']['website'] ?? null,
                    'maps_url' => "https://www.google.com/maps/search/?api=1&query={$cinLat},{$cinLon}",
                ];
            })->sortBy('distance')->values();

            return response()->json([
                'success' => true,
                'cinemas' => $cinemas,
                'count'   => $cinemas->count(),
            ]);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to fetch nearby cinemas.'], 500);
        }
    }

    private function haversine($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; // Earth radius in km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }
}