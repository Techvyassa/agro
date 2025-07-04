<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsnUpload;
use Illuminate\Http\Request;

class AsnUploadController extends Controller
{
    /**
     * Display a listing of ASN uploads, optionally filtered by status.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = AsnUpload::query();
        if ($status) {
            $query->where('status', $status);
        }
        $asnUploads = $query->orderByDesc('created_at')->get();
        return response()->json([
            'success' => true,
            'count' => $asnUploads->count(),
            'data' => $asnUploads
        ]);
    }
} 