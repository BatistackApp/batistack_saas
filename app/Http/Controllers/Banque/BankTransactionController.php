<?php

namespace App\Http\Controllers\Banque;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banque\BankTransactionRequest;
use App\Models\Banque\BankTransaction;

class BankTransactionController extends Controller
{
    public function index()
    {
        return BankTransaction::all();
    }

    public function store(BankTransactionRequest $request)
    {
        return BankTransaction::create($request->validated());
    }

    public function show(BankTransaction $bankTransaction)
    {
        return $bankTransaction;
    }

    public function update(BankTransactionRequest $request, BankTransaction $bankTransaction)
    {
        $bankTransaction->update($request->validated());

        return $bankTransaction;
    }

    public function destroy(BankTransaction $bankTransaction)
    {
        $bankTransaction->delete();

        return response()->json();
    }
}
