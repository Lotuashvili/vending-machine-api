<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email:filter',
            'password' => 'required|min:8',
        ]);

        if (!auth()->attempt($request->only(['email', 'password']))) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = User::where('email', $request->input('email'))->firstOrFail();

        return response()->json([
            'token' => $user->createToken('API')->plainTextToken,
        ]);
    }
}
