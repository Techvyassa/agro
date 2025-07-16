<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Constructor to apply auth middleware
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $products = Products::latest()->paginate(10);
        return view('admin.products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = ['Seeds', 'Fertilizers', 'Tools', 'Pesticides', 'Irrigation', 'Machinery'];
        return view('admin.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
        ]);

        Products::create($request->all());

        return redirect()->route('admin.products.index')
            ->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Products $product)
    {
        return view('admin.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Products $product)
    {
        $categories = ['Seeds', 'Fertilizers', 'Tools', 'Pesticides', 'Irrigation', 'Machinery'];
        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Products $product)
    {
        $request->validate([
            'item_name' => 'required|string|max:255',
            'length' => 'nullable|numeric|min:0',
            'width' => 'nullable|numeric|min:0',
            'height' => 'nullable|numeric|min:0',
            'weight' => 'nullable|numeric|min:0',
        ]);

        $product->update($request->all());

        return redirect()->route('admin.products.index')
            ->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Products $product)
    {
        // Delete the image if it exists
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }
        
        $product->delete();

        return redirect()->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * Handle CSV upload for bulk product creation.
     */
    public function uploadCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $header = null;
        $data = [];
        $rowCount = 0;
        $createdCount = 0;
        if (($handle = fopen($file->getRealPath(), 'r')) !== false) {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                if (!$header) {
                    $header = $row;
                } else {
                    $rowData = array_combine($header, $row);
                    // Basic validation for required fields
                    if (empty($rowData['item_name'])) {
                        continue;
                    }
                    Products::create([
                        'item_name' => $rowData['item_name'],
                        'length' => $rowData['length'] ?? null,
                        'width' => $rowData['width'] ?? null,
                        'height' => $rowData['height'] ?? null,
                        'weight' => $rowData['weight'] ?? null,
                    ]);
                    $createdCount++;
                }
                $rowCount++;
            }
            fclose($handle);
        }
        if ($createdCount > 0) {
            return redirect()->back()->with('csv_success', "$createdCount products uploaded successfully.");
        } else {
            return redirect()->back()->with('csv_error', 'No products were uploaded. Please check your CSV file.');
        }
    }
}
