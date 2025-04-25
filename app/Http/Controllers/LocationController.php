<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class LocationController extends Controller
{
    public function autocomplete(Request $request)
    {
        $response = Http::get('https://api.locationiq.com/v1/autocomplete', [
            'key' => env('LOCATIONIQ_API_KEY'),
            'q' => $request->input('q'),
            'limit' => 5,
            
        ]);

        return $response->json();
    }

    public function reverseGeocode(Request $request)
    {
        $response = Http::get('https://us1.locationiq.com/v1/reverse', [
            'key' => env('LOCATIONIQ_API_KEY'),
            'lat' => $request->input('lat'),
            'lon' => $request->input('lon'),
            'format' => 'json',
        ]);

        return $response->json();
    }
}
