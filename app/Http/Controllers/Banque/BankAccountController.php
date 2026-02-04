<?php

namespace App\Http\Controllers\Banque;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banque\BankAccountRequest;
use App\Models\Banque\BankAccount;
use App\Services\Banque\BankingSyncService;
use Illuminate\Http\JsonResponse;

class BankAccountController extends Controller
{
    public function __construct(protected BankingSyncService $syncService) {}

    public function index(): JsonResponse
    {
        return response()->json(BankAccount::all());
    }

    public function store(BankAccountRequest $request): JsonResponse
    {
        $account = BankAccount::create($request->validated());

        return response()->json($account, 201);
    }

    /**
     * Déclenche une synchronisation manuelle via Bridge V3.
     */
    public function sync(BankAccount $bankAccount): JsonResponse
    {
        try {
            $count = $this->syncService->syncAccount($bankAccount);

            return response()->json([
                'message' => 'Synchronisation réussie.',
                'new_transactions_count' => $count,
                'last_synced_at' => $bankAccount->refresh()->last_synced_at,
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(BankAccount $bankAccount): JsonResponse
    {
        return response()->json($bankAccount);
    }

    public function update(BankAccountRequest $request, BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->update($request->validated());

        return response()->json($bankAccount);
    }

    public function destroy(BankAccount $bankAccount): JsonResponse
    {
        $bankAccount->delete();

        return response()->json(null, 204);
    }
}
