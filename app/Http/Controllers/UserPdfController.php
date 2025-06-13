<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\UserPdf;
use Illuminate\Support\Facades\Auth;

use App\Models\SalesOrder;
use Illuminate\Support\Facades\DB;

class UserPdfController extends Controller
{
    // Show upload form and list
    public function index()
    {
        $pdfs = UserPdf::where('user_id', Auth::id())->get();
        return view('user.pdfs.index', compact('pdfs'));
    }

    // Handle upload and extract invoice data from API
    public function store(Request $request)
    {
        $request->validate([
            'pdf_file' => 'required|file|mimes:pdf|max:20480', // 20MB max
        ]);

        $file = $request->file('pdf_file');
        $fileName = $file->getClientOriginalName();
        $apiUrl = 'http://ec2-54-172-12-118.compute-1.amazonaws.com/agro-api/extract-invoice';

        try {
            $client = new \GuzzleHttp\Client(['verify' => false]);
            $response = $client->request('POST', $apiUrl, [
                'multipart' => [
                    [
                        'name'     => 'file',
                        'contents' => fopen($file->getRealPath(), 'r'),
                        'filename' => $fileName,
                    ],
                ],
                'timeout' => 60,
            ]);

            $body = $response->getBody()->getContents();
            $responseArr = json_decode($body, true);

            // Support both old (array) and new (object) API response
            $invoiceData = [];
            $message = null;
            $filename = null;
            $success = null;
            if (isset($responseArr['data']) && is_array($responseArr['data'])) {
                $invoiceData = $responseArr['data'];
                $message = $responseArr['message'] ?? null;
                $filename = $responseArr['filename'] ?? null;
                $success = $responseArr['success'] ?? null;
            } elseif (is_array($responseArr)) {
                $invoiceData = $responseArr;
            }

            return view('user.pdfs.index', [
                'pdfs' => [], // No uploads stored
                'invoiceData' => $invoiceData,
                'message' => $message,
                'filename' => $filename,
                'apiSuccess' => $success,
                'apiTimestamp' => $responseArr['timestamp'] ?? null,
                'success' => 'Invoice extracted successfully.'
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to extract invoice: ' . $e->getMessage());
        }
    }

    // Download PDF
    public function download($id)
    {
        $pdf = UserPdf::where('id', $id)->where('user_id', Auth::id())->firstOrFail();
        return response($pdf->pdf_data, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'attachment; filename="' . $pdf->file_name . '"');
    }

    // Save extracted invoice data to sales_orders table
    public function saveToSalesOrders(Request $request)
    {
        $soNo = $request->input('so_no');
        $invoiceData = json_decode($request->input('invoice_data'), true);
        $apiTimestamp = $request->input('api_timestamp');
        if (!$soNo || !is_array($invoiceData) || !count($invoiceData) || !$apiTimestamp) {
            return redirect()->back()->with('error', 'Invalid data for saving sales orders. Timestamp required.');
        }
        try {
            DB::statement("SET time_zone = '+05:30'");
            $createdAt = $apiTimestamp;
            foreach ($invoiceData as $row) {
                SalesOrder::create([
                    'so_no' => $soNo,
                    'item_name' => $row['item_name'] ?? '',
                    'category' => $row['category'] ?? '',
                    'hsn' => $row['hsn'] ?? '',
                    'qty' => isset($row['qty']) ? floatval(preg_replace('/[^0-9.]/', '', $row['qty'])) : 0,
                    'rate' => $row['rate'] ?? 0,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
            return redirect()->route('user.pdfs.index')->with('success', 'Sales orders inserted successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to insert sales orders: ' . $e->getMessage());
        }
    }

    //
}
