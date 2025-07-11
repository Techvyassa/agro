<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Response;

class ReportController extends Controller
{


    public function index(Request $request)
    {
        // 1. Get distinct location IDs from transfers table
        $locations = DB::table('transfer')
            ->select('location_id')
            ->whereNotNull('location_id')
            ->distinct()
            ->pluck('location_id');

        // 2. Build the main query with optional location filter
        $query = DB::table('transfer')
            ->leftJoin('asn_uploads', 'transfer.part_no', '=', 'asn_uploads.part_no')
            ->select(
                'transfer.*',
                'asn_uploads.description as asn_description' // alias for clarity
            );

        if ($request->filled('locationcode')) {
            $query->where('transfer.location_id', $request->input('locationcode'));
        }

        // 3. Execute and map created_at formatting
        $reports = $query->get()->map(function ($report) {
            $report->formatted_date = $report->created_at
                ? Carbon::parse($report->created_at)->format('d-m-Y')
                : '-';
            return $report;
        });

        return view('superadmin.reports.index', compact('reports', 'locations'));
    }


    public function export(Request $request)
    {
        // Build the query with optional location filter and join with asn_uploads
        $query = DB::table('transfer')
            ->leftJoin('asn_uploads', 'transfer.part_no', '=', 'asn_uploads.part_no')
            ->select(
                'transfer.*',
                'asn_uploads.description as asn_description'
            );

        if ($request->filled('locationcode')) {
            $query->where('transfer.location_id', $request->input('locationcode'));
        }

        $transfers = $query->get();

        $filename = 'transfer_export_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($transfers) {
            $handle = fopen('php://output', 'w');

            // CSV Header
            fputcsv($handle, [
                'Sr No.',
                'Location ID',
                'Invoice Number',
                'Part No',
                'Description',
                'Transfer Qty',
                'Rack',
                'Created At'
            ]);

            // CSV Data
            foreach ($transfers as $index => $transfer) {
                fputcsv($handle, [
                    $index + 1,
                    $transfer->location_id,
                    $transfer->invoice_number,
                    $transfer->part_no,
                    $transfer->asn_description ?? '-',
                    $transfer->transfer_qty,
                    $transfer->rack,
                    $transfer->created_at ? Carbon::parse($transfer->created_at)->format('Y-m-d') : '-'
                ]);
            }

            fclose($handle);
        };

        return Response::stream($callback, 200, $headers);
    }




}
