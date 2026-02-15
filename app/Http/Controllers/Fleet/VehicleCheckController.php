<?php

namespace App\Http\Controllers\Fleet;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fleet\VehicleCheckRequest;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleCheck;
use App\Models\Fleet\VehicleChecklistTemplate;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleCheckController extends Controller
{
    /**
     * Liste paginée des contrôles (Historique pour le back-office).
     */
    public function index(Request $request): JsonResponse
    {
        $checks = VehicleCheck::with(['vehicle', 'user'])
            ->when($request->vehicle_id, fn($q) => $q->where('vehicle_id', $request->vehicle_id))
            ->when($request->has_anomalie, fn($q) => $q->where('has_anomalie', true))
            ->latest()
            ->paginate(20);

        return response()->json($checks);
    }

    /**
     * Récupère le questionnaire actif pour un véhicule donné.
     * Appelé par l'application mobile avant de démarrer un contrôle.
     */
    public function getTemplateForVehicle(Vehicle $vehicle): JsonResponse
    {
        $template = VehicleChecklistTemplate::where('vehicle_type', $vehicle->type)
            ->where('is_active', true)
            ->with('questions')
            ->first();

        if (!$template) {
            return response()->json([
                'error' => 'Aucun formulaire de sécurité configuré pour ce type de véhicule.'
            ], 404);
        }

        return response()->json($template);
    }

    /**
     * Soumission d'un contrôle complet (Prise/Fin de poste).
     */
    public function store(VehicleCheckRequest $request): JsonResponse
    {
        return DB::transaction(function () use ($request) {
            // 1. Création du rapport de contrôle
            $check = VehicleCheck::create([
                'vehicle_id'            => $request->vehicle_id,
                'user_id'               => auth()->id(),
                'vehicle_assignment_id' => $request->vehicle_assignment_id,
                'type'                  => $request->type,
                'odometer_reading'      => $request->odometer_reading,
                'general_note'          => $request->general_note,
            ]);

            // 2. Enregistrement des réponses aux questions
            $results = collect($request->input('results'))->map(function ($res) {
                return [
                    'question_id'         => $res['question_id'],
                    'value'               => $res['value'],
                    'anomaly_description' => $res['anomaly_description'] ?? null,
                    'is_anomaly'          => $res['value'] === 'ko',
                    'photo_path'          => $res['photo_path'] ?? null,
                ];
            });

            $check->results()->createMany($results->toArray());

            // 3. Analyse globale pour marquer si le contrôle contient une anomalie
            $hasAnomalie = $results->contains('is_anomaly', true);
            $check->update(['has_anomalie' => $hasAnomalie]);

            // 4. Mise à jour automatique de l'odomètre actuel du véhicule
            $check->vehicle->update(['current_odometer' => $request->odometer_reading]);

            return response()->json([
                'message' => 'Contrôle enregistré avec succès.',
                'check'   => $check->load('results')
            ], 201);
        });
    }

    /**
     * Détails d'un rapport de contrôle spécifique.
     */
    public function show(VehicleCheck $check): JsonResponse
    {
        return response()->json($check->load(['vehicle', 'user', 'results.question']));
    }
}
