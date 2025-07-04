<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\AsnUpload;

class LocationDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('location-dashboard');
    }

    public function asnUpload()
    {
        return view('location-asn-upload');
    }

    public function asnUploadPost(Request $request)
    {
        // Handle confirmation and DB insert
        if ($request->filled('asn_confirmed') && $request->filled('asn_data')) {
            $asn = json_decode($request->input('asn_data'), true);
            $location_id = $request->session()->get('location_id');
            if ($location_id && isset($asn['invoice_number'], $asn['timestamp'], $asn['items']) && is_array($asn['items'])) {
                foreach ($asn['items'] as $item) {
                    AsnUpload::create([
                        'location_id' => $location_id,
                        'invoice_number' => $asn['invoice_number'],
                        'asn_timestamp' => $asn['timestamp'],
                        'sr' => $item['Sr'] ?? null,
                        'description' => $item['Description'] ?? null,
                        'part_no' => $item['Part No'] ?? null,
                        'model' => $item['Model'] ?? null,
                        'pcs' => $item['PCS'] ?? null,
                    ]);
                }
                return redirect()->route('location.asn.upload')->with('success', 'ASN data inserted successfully!');
            } else {
                return back()->with('error', 'Missing required ASN data or location.');
            }
        }

        $request->validate([
            'asn_file' => 'required|file|mimes:pdf|max:10240', // 10MB max
        ]);

        // Ensure 'tmp' directory exists
        if (!Storage::exists('tmp')) {
            Storage::makeDirectory('tmp');
        }

        // Save uploaded file to storage/app/tmp
        $file = $request->file('asn_file');
        $filename = uniqid('asn_') . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('tmp', $filename);
        $fullPath = Storage::path($path);

        // Send file to FastAPI endpoint using Http facade
        try {
            $response = \Illuminate\Support\Facades\Http::attach(
                'file',
                file_get_contents($fullPath),
                $filename
            )->post('http://ec2-52-205-180-161.compute-1.amazonaws.com/agro-api/extract-asn/', [
                // No additional fields
            ]);

            $api_response = $response->json();
        } catch (\Exception $e) {
            $api_response = [
                'success' => false,
                'error' => 'API request failed: ' . $e->getMessage(),
            ];
        }

        // Optionally delete the temp file
        // Storage::delete($path);

        // Show the extracted data in the view for confirmation
        return view('location-asn-upload', [
            'api_response' => $api_response,
        ]);
    }

    public function asnUploadList(Request $request)
    {
        $location_id = $request->session()->get('location_id');
        $invoice_number = $request->query('invoice_number');
        $asn_uploads = [];
        if ($location_id) {
            $query = \App\Models\AsnUpload::where('location_id', $location_id);
            if ($invoice_number) {
                $query->where('invoice_number', 'like', "%$invoice_number%");
            }
            $asn_uploads = $query->orderByDesc('created_at')->get();
        }
        return view('location-asn-list', compact('asn_uploads', 'invoice_number'));
    }
} 