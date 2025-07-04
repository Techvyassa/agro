<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        if (!session('superadmin_id')) {
            return redirect()->route('superadmin.login');
        }
        return view('superadmin.dashboard');
    }
} 