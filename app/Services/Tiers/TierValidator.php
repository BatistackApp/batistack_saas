<?php

namespace App\Services\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Support\Facades\Validator;

class TierValidator
{
    public function validateForCreation(array $data): array
    {
        return [
            'type_entite' => ['required', 'in:personne_physique,personne_morale'],
            'raison_social' => ['required_if:type_entite,personne_morale', 'string', 'max:255'],
            'nom' => ['required_if:type_entite,personne_physique', 'string', 'max:100'],
            'prenom' => ['required_if:type_entite,personne_physique', 'string', 'max:100'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'code_postal' => ['nullable', 'string', 'regex:/^\d{5}$/'],
            'ville' => ['nullable', 'string', 'max:100'],
            'pays' => ['nullable', 'string', 'max:2'],
            'telephone' => ['nullable', 'string', 'regex:/^[+\d\s\-()]{10,}$/'],
            'email' => ['nullable', 'email', 'unique:tiers,email'],
            'site_web' => ['nullable', 'url'],
            'siret' => ['nullable', 'string', 'regex:/^\d{14}$/', 'unique:tiers,siret'],
            'numero_tva' => ['nullable', 'string', 'unique:tiers,numero_tva'],
            'code_naf' => ['nullable', 'string', 'regex:/^\d{4}[A-Z]$/'],
            'iban' => ['nullable', 'string', 'regex:/^[A-Z]{2}\d{2}[A-Z0-9]+$/'],
            'bic' => ['nullable', 'string', 'regex:/^[A-Z0-9]{8,11}$/'],
            'delai_paiement_days' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function validateForUpdate(Tiers $tier, array $data): array
    {
        $rules = $this->validateForCreation($data);

        // Exclude current email from unique check
        if (isset($rules['email'])) {
            $rules['email'] = ['nullable', 'email', 'unique:tiers,email,'.$tier->id];
        }

        // Exclude current SIRET from unique check
        if (isset($rules['siret'])) {
            $rules['siret'] = ['nullable', 'string', 'regex:/^\d{14}$/', 'unique:tiers,siret,'.$tier->id];
        }

        // Exclude current TVA from unique check
        if (isset($rules['numero_tva'])) {
            $rules['numero_tva'] = ['nullable', 'string', 'unique:tiers,numero_tva,'.$tier->id];
        }

        return $rules;
    }

    public function getCustomMessages(): array
    {
        return [
            'code_postal.regex' => __('validation.code_postal_format'),
            'telephone.regex' => __('validation.telephone_format'),
            'siret.regex' => __('validation.siret_format'),
            'code_naf.regex' => __('validation.code_naf_format'),
            'iban.regex' => __('validation.iban_format'),
            'bic.regex' => __('validation.bic_format'),
        ];
    }

    /**
     * Valide la structure complète d'un tiers avant enregistrement.
     */
    public function validate(array $data)
    {
        return Validator::make($data, [
            'siret' => ['nullable', 'string', 'size:14', function ($attribute, $value, $fail) {
                if (! $this->isLuhnValid($value)) {
                    $fail(__('tiers.validation.siret_invalid'));
                }
            }],
            'iban' => ['nullable', 'string', function ($attribute, $value, $fail) {
                if (! $this->isIbanValid($value)) {
                    $fail(__('tiers.validation.iban_invalid'));
                }
            }],
            'numero_tva' => 'nullable|string|regex:/^[A-Z]{2}[0-9]{11}$/',
        ]);
    }

    /** Algorithme de Luhn pour SIRET */
    private function isLuhnValid($siret): bool
    {
        $siret = str_replace(' ', '', $siret);
        if (! is_numeric($siret) || strlen($siret) != 14) {
            return false;
        }
        $sum = 0;
        for ($i = 0; $i < 14; $i++) {
            $tmp = ((($i + 1) % 2) + 1) * intval($siret[$i]);
            $sum += ($tmp > 9) ? $tmp - 9 : $tmp;
        }

        return $sum % 10 === 0;
    }

    /** Validation IBAN Simplifiée */
    private function isIbanValid($iban): bool
    {
        $iban = strtoupper(str_replace(' ', '', $iban));
        if (! preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{14,30}$/', $iban)) {
            return false;
        }

        // Une logique de validation par modulo 97 pourrait être ajoutée ici ou via une lib externe.
        return true;
    }
}
