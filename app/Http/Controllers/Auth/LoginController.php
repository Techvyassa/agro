<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\LocationUser;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.simple-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            // Check if user exists in location_users table
            $locationUser = DB::table('location_users')->where('email', $request->email)->first();
            if ($locationUser) {
                // Store location_id in session
                $request->session()->put('location_id', $locationUser->location_id);
                return redirect()->intended('/location-dashboard');
            }
            return redirect()->intended('/dashboard');
        }

        // Try location_users table if not found in users
        $locationUser = LocationUser::where('email', $request->email)->first();
        if ($locationUser && Hash::check($request->password, $locationUser->password)) {
            Auth::login($locationUser);
            $request->session()->regenerate();
            // Store location_id in session
            $request->session()->put('location_id', $locationUser->location_id);
            return redirect()->intended('/location-dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
} 