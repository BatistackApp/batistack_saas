<?php

namespace App\Http\Requests\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\User;
use App\Services\Fleet\FleetComplianceService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class VehicleAssignmentRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'user_id' => ['nullable', 'exists:users,id'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Hook de validation supplémentaire pour la logique métier de conformité.
     */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                // On ne vérifie la conformité que si les données de base sont valides
                if ($validator->errors()->hasAny(['vehicle_id', 'user_id'])) {
                    return;
                }

                $userId = $this->input('user_id');

                // Si aucun conducteur n'est assigné (véhicule en parc), on ignore le test
                if (! $userId) {
                    return;
                }

                /** @var FleetComplianceService $complianceService */
                $complianceService = app(FleetComplianceService::class);

                $vehicle = Vehicle::find($this->input('vehicle_id'));
                $user = User::find($userId);

                if (! $vehicle || ! $user) {
                    return;
                }

                // Exécution du moteur de règles
                $check = $complianceService->checkDriverCompliance($vehicle, $user);

                if (! $check['status']) {
                    $validator->errors()->add(
                        'user_id',
                        'Alerte de conformité : '.$check['message']
                    );
                }
            },
        ];
    }

    /**
     * Messages personnalisés pour une meilleure expérience utilisateur.
     */
    public function messages(): array
    {
        return [
            'vehicle_id.required' => 'Veuillez sélectionner un véhicule.',
            'started_at.required' => "La date de début d'affectation est requise.",
            'ended_at.after' => 'La date de fin doit être postérieure à la date de début.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('fleet.manage');
    }
}
