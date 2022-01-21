<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function deposit(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|coin',
        ]);

        $user = $request->user()->deposit($request->input('amount'));

        return response()->json([
            'balance' => $user->balance,
        ], 201);
    }

    public function reset(Request $request): array
    {
        $user = $request->user();

        $user->withdraw($user->balance);

        return [
            'balance' => 0,
        ];
    }
}
