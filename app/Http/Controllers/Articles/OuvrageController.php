<?php

namespace App\Http\Controllers\Articles;

use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\OuvrageRequest;
use App\Models\Articles\Ouvrage;

class OuvrageController extends Controller
{
    public function index()
    {
        return Ouvrage::all();
    }

    public function store(OuvrageRequest $request)
    {
        return Ouvrage::create($request->validated());
    }

    public function show(Ouvrage $ouvrage)
    {
        return $ouvrage;
    }

    public function update(OuvrageRequest $request, Ouvrage $ouvrage)
    {
        $ouvrage->update($request->validated());

        return $ouvrage;
    }

    public function destroy(Ouvrage $ouvrage)
    {
        $ouvrage->delete();

        return response()->json();
    }
}
