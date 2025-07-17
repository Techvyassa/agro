<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
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
}