<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Accounting\ChartOfAccount;
use App\Services\Accounting\BalanceCalculator;
use Illuminate\Http\JsonResponse;

class ChartOfAccountController extends Controller
{
    public function __construct(protected BalanceCalculator $balanceCalculator) {}

    public function index(): JsonResponse
    {
        $accounts = ChartOfAccount::orderBy('account_number')->get();

        return response()->json($accounts);
    }

    /**
     * Récupère le solde actuel d'un compte avec précision BCMath.
     */
    public function getBalance(string $ulid): JsonResponse
    {
        $account = ChartOfAccount::where('ulid', $ulid)->firstOrFail();
        $balance = $this->balanceCalculator->calculate($account, now());

        return response()->json([
            'account' => $account->account_number,
            'balance' => $balance,
            'formatted_balance' => number_format((float) $balance, 2, ',', ' ').' €',
        ]);
    }
}
