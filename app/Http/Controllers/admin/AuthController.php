<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'fullname' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
        ]);
        if ($request) {
            User::create([
                'username' => $request->username,
                'fullname' => $request->fullname,
                'email' => $request->email,
                'password' => Hash::make($request->password)
            ]);
            $response = [
                'message' => 'Đăng ký thành công ! vui lòng đăng nhập'
            ];
            return response($response, 200);
        }
        return response()->json(['message' => 'Đăng ký không thành công'], 401);
    }
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'This field is required.',
            'password.required' => 'This field is required.',
        ]);
        $user = User::where('username', $request->username)->select('id','username', 'fullname', 'email','email_verified_at')->first();

        if (auth()->attempt($request->only('username', 'password'))) {
            $token = $user->createToken('authToken')->plainTextToken;
            $response = [
                'user' => $user,
                'token' => $token
            ];
            return response($response, 200);
        }
        return response()->json(['message' => 'Login failed'], 401);
    }

    public function logout(Request $request)
    {
        auth()->logout();
        return response()->json(['message' => 'Logout successful'], 200);
    }
}
