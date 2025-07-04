<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Superadmin;

class ProfileController extends Controller
{
    public function edit()
    {
        $superadmin = Auth::guard('superadmin')->user();
        return view('superadmin.profile.edit', compact('superadmin'));
    }

    public function update(Request $request)
    {
        $superadmin = Auth::guard('superadmin')->user();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:superadmins,email,' . $superadmin->id,
            'password' => 'nullable|string|min:8|confirmed',
        ]);
        $superadmin->name = $request->name;
        $superadmin->email = $request->email;
        if ($request->filled('password')) {
            $superadmin->password = Hash::make($request->password);
        }
        $superadmin->save();
        return redirect()->route('superadmin.profile.edit')->with('success', 'Profile updated successfully.');
    }
} 