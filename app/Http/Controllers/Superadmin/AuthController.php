<?php
namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Superadmin;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showCreateForm()
    {
        return view('superadmin.create-credential');
    }

    public function createCredential(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:superadmins,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        Superadmin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('superadmin.login')->with('success', 'Superadmin created! Please login.');
    }

    public function showLoginForm()
    {
        return view('auth.simple-login'); // Or a dedicated superadmin login view
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $superadmin = Superadmin::where('email', $request->email)->first();

        if ($superadmin && Hash::check($request->password, $superadmin->password)) {
            session(['superadmin_id' => $superadmin->id]);
            return redirect()->route('superadmin.dashboard');
        }

        return back()->withErrors(['email' => 'Invalid credentials']);
    }
} 