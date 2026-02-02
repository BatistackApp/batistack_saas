<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\InvoicesRequest;
use App\Models\Commerce\Invoices;

class InvoicesController extends Controller
{
    public function index()
    {
        return Invoices::all();
    }

    public function store(InvoicesRequest $request)
    {
        return Invoices::create($request->validated());
    }

    public function show(Invoices $invoices)
    {
        return $invoices;
    }

    public function update(InvoicesRequest $request, Invoices $invoices)
    {
        $invoices->update($request->validated());

        return $invoices;
    }

    public function destroy(Invoices $invoices)
    {
        $invoices->delete();

        return response()->json();
    }
}
