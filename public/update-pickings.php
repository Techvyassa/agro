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
        echo json_encode([
            'success' => false,
            'message' => 'GET method not supported for updating'
        ]);
        exit;
    }

    // 🔍 Parse raw input
    $rawData = json_decode(file_get_contents('php://input'), true);
    if (!$rawData && !empty($_POST)) {
        $rawData = $_POST;
    }

    // Ensure input is an array
    $records = isset($rawData[0]) ? $rawData : [$rawData];

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

    $results = [];

    foreach ($records as $index => $data) {
        try {
            $id = $data['id'] ?? null;
            $box = $data['box'] ?? null;
            $so_no = $data['so_no'] ?? null;
            $dimension = $data['dimension'] ?? null;
            $weight = $data['weight'] ?? null;
            $items = $data['items'] ?? null;

            $pickingModel = null;

            // 🆔 Find by ID or so_no + box
            if ($id) {
                $pickingModel = App\Models\Picking::find($id);
            } else {
                if (empty($box) || empty($so_no)) {
                    throw new Exception('Missing required fields: either id or (box and so_no)');
                }

                $firstRequestBox = extractFirstBoxValue($box);
                if (!$firstRequestBox) {
                    throw new Exception('Unable to extract first box value');
                }

                $allPickings = DB::table('pickings')->where('so_no', $so_no)->get();
                foreach ($allPickings as $picking) {
                    $firstDbBox = extractFirstBoxValue($picking->box);
                    if ($firstDbBox == $firstRequestBox) {
                        $pickingModel = App\Models\Picking::find($picking->id);
                        break;
                    }
                }
            }

            if (!$pickingModel) {
                throw new Exception('Picking not found for ID or box+so_no combination');
            }

            // 🛠️ Update fields
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
                $results[] = [
                    'index' => $index,
                    'success' => true,
                    'message' => 'Picking updated successfully',
                    'id' => $pickingModel->id
                ];
            } else {
                $results[] = [
                    'index' => $index,
                    'success' => true,
                    'message' => 'No fields updated',
                    'id' => $pickingModel->id
                ];
            }

        } catch (Exception $e) {
            // ⛔ Catch and report error for this index
            $results[] = [
                'index' => $index,
                'success' => false,
                'message' => 'Failed to update picking',
                'error' => $e->getMessage(),
                'data' => $data
            ];
        }
    }

    echo json_encode([
        'success' => true,
        'message' => 'Batch update processed',
        'results' => $results
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Critical error during batch update',
        'error' => $e->getMessage()
    ]);
}
