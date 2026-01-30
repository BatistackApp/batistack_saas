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
            'siret' => ['nullable', 'string', 'size:14', fn ($a, $v, $f) => $this->checkLuhn($v) ?: $f(__('SIRET invalide'))],
            'iban' => ['nullable', 'string', fn ($a, $v, $f) => $this->checkIban($v) ?: $f(__('IBAN invalide'))],
        ]);
    }

    private function checkLuhn($val): bool
    {
        $val = preg_replace('/\D/', '', $val);
        if (strlen($val) !== 14) {
            return false;
        }
        $sum = 0;
        for ($i = 0; $i < 14; $i++) {
            $n = (int) $val[13 - $i];
            if ($i % 2 === 1) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
        }

        return $sum % 10 === 0;
    }

    private function checkIban($iban): bool
    {
        $iban = strtoupper(str_replace(' ', '', $iban));
        if (! preg_match('/^[A-Z]{2}[0-9]{2}[A-Z0-9]{12,30}$/', $iban)) {
            return false;
        }

        // Algorithme Modulo 97
        $charMap = ['A' => 10, 'B' => 11, 'C' => 12, 'D' => 13, 'E' => 14, 'F' => 15, 'G' => 16, 'H' => 17, 'I' => 18, 'J' => 19, 'K' => 20, 'L' => 21, 'M' => 22, 'N' => 23, 'O' => 24, 'P' => 25, 'Q' => 26, 'R' => 27, 'S' => 28, 'T' => 29, 'U' => 30, 'V' => 31, 'W' => 32, 'X' => 33, 'Y' => 34, 'Z' => 35];
        $checkString = substr($iban, 4).substr($iban, 0, 4);
        $numericIban = '';
        foreach (str_split($checkString) as $char) {
            $numericIban .= is_numeric($char) ? $char : $charMap[$char];
        }

        return bcmod($numericIban, '97') === '1';
    }
}
