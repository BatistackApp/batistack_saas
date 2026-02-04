<?php

namespace App\Http\Controllers\Banque;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banque\PaymentRequest;
use App\Models\Banque\Payment;

class PaymentController extends Controller
{
    public function index()
    {
        return Payment::all();
    }

    public function store(PaymentRequest $request)
    {
        return Payment::create($request->validated());
    }

    public function show(Payment $payment)
    {
        return $payment;
    }

    public function update(PaymentRequest $request, Payment $payment)
    {
        $payment->update($request->validated());

        return $payment;
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return response()->json();
    }
}
