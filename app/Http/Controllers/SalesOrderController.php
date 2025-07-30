<?php

namespace App\Http\Controllers;

use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SalesOrderController extends Controller
{
    /**
     * Constructor to ensure user is authenticated
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of sales orders
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        // Base SalesOrder query
        $query = \App\Models\SalesOrder::query();

        // Search filter
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('so_no', 'like', "%{$request->search}%")
                    ->orWhere('item_name', 'like', "%{$request->search}%");
            });
        }

        // Uploaded date filter
        if ($request->filled('uploaded_date')) {
            $query->whereDate('created_at', $request->uploaded_date);
        }

        // Initial SO groups with min uploaded date
        $soGroupsQuery = $query->select('so_no', DB::raw('MIN(created_at) as uploaded_at'))->groupBy('so_no');

        // Packing date filter (based on pickings.updated_at)
        if ($request->filled('packing_date')) {
            $soGroupsQuery->whereIn('so_no', function ($sub) use ($request) {
                $sub->select('so_no')
                    ->from('pickings')
                    ->whereDate('updated_at', $request->packing_date);
            });
        }

        // Paginate final SO groups
        $soGroups = $soGroupsQuery->orderByDesc('uploaded_at')->paginate(50)->appends($request->all());

        // Load related sales orders & pickings
        $soNos = $soGroups->pluck('so_no');
        $salesOrdersBySo = \App\Models\SalesOrder::whereIn('so_no', $soNos)->get()->groupBy('so_no');

        $pickingsBySo = \App\Models\Picking::whereIn('so_no', $soNos)->get()->groupBy('so_no');
       
        // Decode items JSON in pickings
            $pickingsBySo->each(function ($group) {
                $group->each(function ($picking) {
                    $picking->items_array = $picking->items;
                });
            });

        return view('sales_orders.index', [
            'soGroups' => $soGroups,
            'salesOrdersBySo' => $salesOrdersBySo,
            'pickingsBySo' => $pickingsBySo,
            'search' => $request->search,
            'uploaded_date' => $request->uploaded_date,
            'packing_date' => $request->packing_date,
        ]);
    }


    /**
     * Filter sales orders by SO Number
     *
     * @param  Request  $request
     * @return \Illuminate\View\View
     */
    public function filter(Request $request)
    {
        $query = SalesOrder::query();
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('so_no', 'like', "%$search%")
                    ->orWhere('item_name', 'like', "%$search%");
            });
        }
        $salesOrders = $query->orderByDesc('id')->paginate(15)->appends($request->all());
        return view('sales_orders.index', compact('salesOrders'));
    }

    /**
     * Export sales orders to CSV
     *
     * @param  Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function export(Request $request)
    {
        $so_no = $request->input('so_no');

        if ($so_no) {
            $salesOrders = SalesOrder::where('so_no', $so_no)
                ->orderBy('so_no')
                ->get();
        } else {
            $salesOrders = SalesOrder::orderBy('so_no')
                ->get();
        }

        $filename = 'sales_orders_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($salesOrders) {
            $file = fopen('php://output', 'w');

            // Add headers
            fputcsv($file, ['SO Number', 'Item Name', 'Category', 'HSN Code', 'Quantity', 'Rate', 'Amount', 'Date Added']);

            // Add data
            foreach ($salesOrders as $order) {
                fputcsv($file, [
                    $order->so_no,
                    $order->item_name,
                    $order->category,
                    $order->hsn,
                    $order->qty,
                    $order->rate,
                    $order->qty * $order->rate,
                    ($order->created_at) ? $order->created_at->format('Y-m-d') : ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show the Sales Order CSV upload form
     *
     * @return \Illuminate\View\View
     */
    public function showUploadForm()
    {
        return view('sales_orders.upload');
    }

    /**
     * Process the uploaded CSV file
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function processUpload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $file = $request->file('csv_file');
        $handle = fopen($file->getPathname(), 'r');

        // Get headers from first row
        $headers = fgetcsv($handle);

        // Convert headers to lowercase
        $headers = array_map('strtolower', $headers);

        // Check required columns exist
        $requiredColumns = ['so_no', 'item_name', 'category', 'hsn', 'qty', 'rate'];
        $missingColumns = array_diff($requiredColumns, $headers);

        if (count($missingColumns) > 0) {
            return redirect()->back()->with('error', 'CSV is missing required columns: ' . implode(', ', $missingColumns));
        }

        // Read data
        $salesOrders = [];
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= count($headers)) {
                $row = array_combine($headers, $data);
                $salesOrders[] = $row;
            }
        }

        fclose($handle);

        if (empty($salesOrders)) {
            return redirect()->back()->with('error', 'No valid data found in the CSV file.');
        }

        // Group data by SO number for better display
        $groupedOrders = [];
        foreach ($salesOrders as $order) {
            $soNumber = $order['so_no'];
            if (!isset($groupedOrders[$soNumber])) {
                $groupedOrders[$soNumber] = [];
            }
            $groupedOrders[$soNumber][] = $order;
        }

        // Store in session and display for confirmation
        session(['sales_orders' => $salesOrders]);
        session(['grouped_orders' => $groupedOrders]);

        return view('sales_orders.confirm', [
            'salesOrders' => $salesOrders,
            'groupedOrders' => $groupedOrders
        ]);
    }

    /**
     * Save the confirmed sales order data
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function saveOrders(Request $request)
    {
        $salesOrders = session('sales_orders', []);

        if (empty($salesOrders)) {
            return redirect()->route('sales_orders.upload')->with('error', 'No sales order data found. Please upload again.');
        }

        try {
            // Begin transaction to ensure all records are saved or none
            DB::beginTransaction();

            // Save data to database
            foreach ($salesOrders as $order) {
                SalesOrder::create([
                    'so_no' => $order['so_no'],
                    'item_name' => $order['item_name'],
                    'category' => $order['category'],
                    'hsn' => $order['hsn'],
                    'qty' => $order['qty'],
                    'rate' => $order['rate']
                ]);
            }

            // Commit transaction
            DB::commit();

            // Clear the session
            session()->forget(['sales_orders', 'grouped_orders']);

            return redirect()->route('sales_orders.index')->with('success', count($salesOrders) . ' sales orders have been successfully imported.');

        } catch (\Exception $e) {
            // Rollback if any error occurs
            DB::rollBack();

            // Log the error
            Log::error('Error saving sales orders: ' . $e->getMessage());

            return redirect()->route('sales_orders.upload')->with('error', 'Error saving sales orders: ' . $e->getMessage());
        }
    }
}