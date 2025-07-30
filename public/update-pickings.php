<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

header('Content-Type: application/json');

try {
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        $id = $_GET['id'] ?? null;
        $box = $_GET['box'] ?? null;
        $so_no = $_GET['so_no'] ?? null;
        $dimension = $_GET['dimension'] ?? null;
        $weight = $_GET['weight'] ?? null;
        $items = isset($_GET['items']) ? (is_array($_GET['items']) ? $_GET['items'] : [$_GET['items']]) : null;
    } else {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data && !empty($_POST)) {
            $data = $_POST;
        }
        $id = $data['id'] ?? null;
        $box = $data['box'] ?? null;
        $so_no = $data['so_no'] ?? null;
        $dimension = $data['dimension'] ?? null;
        $weight = $data['weight'] ?? null;
        $items = $data['items'] ?? null;
    }

    function extractFirstBoxValue($box)
    {
        if (is_array($box)) {
            return trim($box[0]);
        }

        if (is_string($box)) {
            $clean = trim($box, "[] ");
            $parts = explode(',', $clean);
            return isset($parts[0]) ? trim($parts[0], "\"' ") : null;
        }

        return null;
    }

    $pickingModel = null;

    // 🔍 Directly find by ID if provided
    if ($id) {
        $pickingModel = App\Models\Picking::find($id);
        if (!$pickingModel) {
            echo json_encode([
                'success' => false,
                'message' => 'No picking found with provided ID'
            ]);
            exit;
        }
    } else {
        // Require so_no and box if no ID
        if (empty($box) || empty($so_no)) {
            echo json_encode([
                'success' => false,
                'message' => 'Either id or both box and so_no parameters are required'
            ]);
            exit;
        }

        $firstRequestBox = extractFirstBoxValue($box);
        if (!$firstRequestBox) {
            echo json_encode([
                'success' => false,
                'message' => 'Unable to extract first box value from request'
            ]);
            exit;
        }

        // 🔍 Find all pickings for so_no and match box
        $allPickings = DB::table('pickings')->where('so_no', $so_no)->get();

        foreach ($allPickings as $picking) {
            $firstDbBox = extractFirstBoxValue($picking->box);
            if ($firstDbBox == $firstRequestBox) {
                $pickingModel = App\Models\Picking::find($picking->id);
                break;
            }
        }

        if (!$pickingModel) {
            echo json_encode([
                'success' => false,
                'message' => 'No matching picking found for given so_no and box'
            ]);
            exit;
        }
    }

    // 🔧 Update fields if provided
    $updated = false;

    if (!is_null($items)) {
        $pickingModel->items = $items;
        $updated = true;
    }

    if (!is_null($box)) {
        $pickingModel->box = $box;
        $updated = true;
    }

    if (!is_null($dimension)) {
        $pickingModel->dimension = $dimension;
        $updated = true;
    }

    if (!is_null($weight)) {
        $pickingModel->weight = $weight;
        $updated = true;
    }

    if ($updated) {
        $pickingModel->save();
        echo json_encode([
            'success' => true,
            'message' => 'Picking updated successfully',
            'data' => $pickingModel
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'No fields updated',
            'data' => $pickingModel
        ]);
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error occurred during update',
        'error' => $e->getMessage()
    ]);
}
