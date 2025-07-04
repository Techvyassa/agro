<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Location;
use App\Models\LocationUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create()
    {
        $locations = Location::all();
        return view('superadmin.users.create', compact('locations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:location_users',
            'password' => 'required|string|min:8|confirmed',
            'location_id' => 'required|exists:locations,id',
        ]);

        LocationUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'location_id' => $request->location_id,
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User created successfully.');
    }

    public function index()
    {
        $users = LocationUser::with('location')->paginate(10);
        return view('superadmin.users.index', compact('users'));
    }

    public function edit($id)
    {
        $user = LocationUser::findOrFail($id);
        $locations = Location::all();
        return view('superadmin.users.edit', compact('user', 'locations'));
    }

    public function update(Request $request, $id)
    {
        $user = LocationUser::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:location_users,email,' . $user->id,
            'location_id' => 'required|exists:locations,id',
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'location_id' => $request->location_id,
        ];
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        return redirect()->route('superadmin.users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = LocationUser::findOrFail($id);
        $user->delete();
        return redirect()->route('superadmin.users.index')->with('success', 'User deleted successfully.');
    }
} 