<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TrackStatusController extends Controller
{
    /**
     * Show the track status page
     */
    public function index()
    {
        $courierServices = [];
        
        try {
            $client = new Client([
                'timeout' => 10,
                'headers' => [
                    'Accept' => 'application/json',
                ]
            ]);
            
            $response = $client->get('https://03a6-106-222-208-39.ngrok-free.app/courier-services', [
                'http_errors' => false,
            ]);
            
            if ($response->getStatusCode() == 200) {
                $data = json_decode($response->getBody()->getContents(), true);
                if (isset($data['services']) && is_array($data['services'])) {
                    $courierServices = $data['services'];
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch courier services: ' . $e->getMessage());
            // Fallback to default services if API fails
            $courierServices = [
                ['name' => 'Delhivery', 'code' => 'delhivery'],
                ['name' => 'BigShip', 'code' => 'bigship']
            ];
        }
        
        return view('track.index', compact('courierServices'));
    }

    /**
     * Track shipment status via API
     */
    public function trackStatus(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string',
            'search_value' => 'required|string',
            '_token' => 'sometimes',
        ]);

        $client = new Client([
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ]);

        try {
            $response = $client->post('https://03a6-106-222-208-39.ngrok-free.app/track-status', [
                'json' => [
                    'service_name' => $request->service_name,
                    'search_value' => $request->search_value,
                ],
                'http_errors' => false,
            ]);

            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);

            return response()->json($responseBody, $statusCode);
        } catch (\Exception $e) {
            Log::error('Track Status API error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Error connecting to tracking service',
                'message' => $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }
}
