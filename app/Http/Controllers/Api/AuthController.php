<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Handle user login and return token
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('authToken')->plainTextToken;

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'data' => [
                    'user' => $user,
                    'token' => $token
                ]
            ], 200);
        }

        // Check location_users table if not found in users
        $locationUser = \App\Models\LocationUser::where('email', $request->email)->first();
        if ($locationUser && \Illuminate\Support\Facades\Hash::check($request->password, $locationUser->password)) {
            // Optionally, you can generate a token for location users if needed
            // $token = $locationUser->createToken('authToken')->plainTextToken;
            return response()->json([
                'status' => 'success',
                'message' => 'Login successful (location user)',
                'data' => [
                    'location_id' => $locationUser->location_id,
                    'name' => $locationUser->name,
                    // 'token' => $token // Uncomment if you want to provide a token
                ]
            ], 200);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Invalid credentials'
        ], 401);
    }

    /**
     * Logout user
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully'
        ], 200);
    }
}
