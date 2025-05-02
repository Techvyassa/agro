<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Products;
use App\Models\SalesOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:admin');
    }

    public function index()
    {
        // Get counts for dashboard widgets
        $userCount = User::count();
        $itemCount = Products::count();
        $salesOrderCount = SalesOrder::count();
        
        // Calculate total revenue from sales orders
        $totalRevenue = SalesOrder::selectRaw('SUM(qty * rate) as total_revenue')
            ->first()
            ->total_revenue ?? 0;
        
        // Get recent items
        $recentItems = Products::latest()->take(5)->get();
        
        // Get recent sales orders
        $recentSalesOrders = SalesOrder::latest()->take(5)->get();
        
        // Category distribution is skipped due to database structure differences
        $categoryDistribution = collect(); // Empty collection to avoid template errors
        
        return view('admin.dashboard', compact(
            'userCount', 
            'itemCount', 
            'salesOrderCount', 
            'totalRevenue', 
            'recentItems', 
            'recentSalesOrders',
            'categoryDistribution'
        ));
    }
}