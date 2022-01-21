<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email:filter',
            'password' => 'required|min:8',
        ]);

        if (!auth()->attempt($data)) {
            return response()->json([
                'message' => 'Invalid credentials.',
            ], 401);
        }

        $user = User::where('email', $request->input('email'))->firstOrFail();

        return response()->json([
            'token' => $user->createToken('API')->plainTextToken,
        ]);
    }

    public function register(Request $request): UserResource
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email:filter|max:255|unique:users',
            'password' => 'required|min:8|max:255|confirmed',
            'role' => 'required|string|max:255|exists:roles,name',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ])->assignRole($request->input('role'));

        return new UserResource($user->load('roles'));
    }

    public function logout(Request $request): Response
    {
        $request->user()->currentAccessToken()->delete();

        return response()->noContent();
    }
}
