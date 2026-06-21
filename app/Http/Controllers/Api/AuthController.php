<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Peserta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'identity' => 'required|string',
            'password' => 'required|string',
        ]);

        $identity = $request->input('identity');
        $password = $request->input('password');

        // Check if format is a Nomor Peserta (starts with P/p followed by numbers)
        // If so, authenticate against the Peserta table. Otherwise, authenticate against the Admin table.
        if (preg_match('/^[pP]\d+$/', $identity)) {
            $peserta = Peserta::where('nomor_peserta', $identity)->where('status', 'aktif')->first();
            if ($peserta && Hash::check($password, $peserta->password)) {
                $token = $peserta->createToken('peserta-token')->plainTextToken;
                return response()->json([
                    'type' => 'peserta',
                    'user' => $peserta,
                    'token' => $token,
                ]);
            }
        } else {
            $admin = Admin::where('username', $identity)->first();
            if ($admin && Hash::check($password, $admin->password)) {
                $token = $admin->createToken('admin-token')->plainTextToken;
                return response()->json([
                    'type' => 'admin',
                    'user' => $admin,
                    'token' => $token,
                ]);
            }
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
