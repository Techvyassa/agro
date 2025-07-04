<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AsnUpdateController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->only(['invoice_number', 'part_no', 'status', 'inward_qty']);

        // Validate input
        $validated = $request->validate([
            'invoice_number' => 'required|string',
            'part_no'        => 'required|string',
            'status'         => 'required|string',
            'inward_qty'     => 'required|integer',
        ]);

        // Check if the record exists
        $record = DB::table('asn_uploads')
            ->where('invoice_number', $data['invoice_number'])
            ->where('part_no', $data['part_no'])
            ->first();

        if ($record) {
            // Update the record
            DB::table('asn_uploads')
                ->where('invoice_number', $data['invoice_number'])
                ->where('part_no', $data['part_no'])
                ->update([
                    'status' => $data['status'],
                    'inward_qty' => $data['inward_qty'],
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Record updated successfully.'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Record not found.'
            ]);
        }
    }
} 