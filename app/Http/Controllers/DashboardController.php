<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Share the count of SOs in short (pickings on hold) with all dashboard views
        $shortSoCount = \App\Models\Picking::where('status', 'hold')->get()->groupBy('so_no')->count();
        view()->share('shortSoCount', $shortSoCount);
    }

    public function index()
    {
        // Fetch pickings with status 'hold' for Short SO
        $shortSOs = \App\Models\Picking::where('status', 'hold')->get();
        // Display the dashboard page
        return view('dashboard', compact('shortSOs'));
    }

    public function forceComplete($id)
    {
        $picking = \App\Models\Picking::find($id);
        if ($picking && $picking->status === 'hold') {
            $picking->status = 'completed';
            $picking->save();
            return redirect()->back()->with('success', 'SO marked as completed.');
        }
        return redirect()->back()->with('error', 'Picking not found or not on hold.');
    }

    public function shortSoPage()
    {
        // Get all pickings on hold, grouped by so_no
        $pickings = \App\Models\Picking::where('status', 'hold')->get()->groupBy('so_no');
        return view('short-so', compact('pickings'));
    }

    public function forceCompleteBySoNo($so_no)
    {
        $updated = \App\Models\Picking::where('so_no', $so_no)->where('status', 'hold')->update(['status' => 'completed']);
        if ($updated) {
            return redirect()->route('short-so')->with('success', 'All pickings for SO ' . $so_no . ' marked as completed.');
        }
        return redirect()->route('short-so')->with('error', 'No pickings found or already completed for SO ' . $so_no);
    }
}