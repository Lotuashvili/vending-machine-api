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

        $coins = config('app.coins');
        rsort($coins);

        $balance = $user->balance;

        if ($balance <= 0) {
            return [
                'balance' => 0,
                'change' => [],
                'finalBalance' => 0,
            ];
        }

        $change = [];

        // Return the change with the highest coins possible
        $finalBalance = array_reduce($coins, function ($balance, $coin) use (&$change) {
            $amount = floor($balance / $coin);

            if ($amount > 0) {
                $change[$coin] = $amount;
            }

            return $balance - $amount * $coin;
        }, $balance);

        $user->withdraw($balance);

        return [
            'balance' => $balance,
            'change' => $change,
            'finalBalance' => $finalBalance,
        ];
    }
}
