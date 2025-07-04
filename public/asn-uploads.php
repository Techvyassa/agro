<?php
// Direct ASN Uploads JSON endpoint

// Bootstrap Laravel
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

header('Content-Type: application/json');

try {
    $status = isset($_GET['status']) ? $_GET['status'] : null;
    $query = \App\Models\AsnUpload::query();
    if ($status) {
        $query->where('status', $status);
    }
    $asnUploads = $query->orderByDesc('created_at')->get();
    echo json_encode([
        'success' => true,
        'count' => $asnUploads->count(),
        'data' => $asnUploads
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to fetch ASN uploads',
        'error' => $e->getMessage()
    ]);
} 