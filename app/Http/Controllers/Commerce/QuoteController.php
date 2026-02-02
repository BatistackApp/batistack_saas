<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\QuoteRequest;
use App\Models\Commerce\Quote;

class QuoteController extends Controller
{
    public function index()
    {
        return Quote::all();
    }

    public function store(QuoteRequest $request)
    {
        return Quote::create($request->validated());
    }

    public function show(Quote $quote)
    {
        return $quote;
    }

    public function update(QuoteRequest $request, Quote $quote)
    {
        $quote->update($request->validated());

        return $quote;
    }

    public function destroy(Quote $quote)
    {
        $quote->delete();

        return response()->json();
    }
}
