<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Location;

class LocationController extends Controller
{
    public function index() {
        $locations = Location::all();
        return view('superadmin.locations.index', compact('locations'));
    }
    public function create() {
        return view('superadmin.locations.create');
    }
    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code',
        ]);
        Location::create($request->only('name', 'code'));
        return redirect()->route('superadmin.locations.index')->with('success', 'Location created successfully.');
    }
    public function edit(Location $location) {
        return view('superadmin.locations.edit', compact('location'));
    }
    public function update(Request $request, Location $location) {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code,' . $location->id,
        ]);
        $location->update($request->only('name', 'code'));
        return redirect()->route('superadmin.locations.index')->with('success', 'Location updated successfully.');
    }
    public function destroy(Location $location) {
        $location->delete();
        return redirect()->route('superadmin.locations.index')->with('success', 'Location deleted successfully.');
    }
} 