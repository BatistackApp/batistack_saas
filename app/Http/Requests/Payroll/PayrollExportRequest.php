<?php

namespace App\Http\Requests\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class PayrollExportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'format' => ['required', 'in:csv,txt,sage,cegid'],
            'recipient_email' => ['required', 'email'],
            'include_employer_charges' => ['boolean'],
            'group_by_project' => ['boolean'], // Pour l'analytique
        ];
    }

    public function messages(): array
    {
        return [
            'format.in' => 'Le format d\'export sélectionné n\'est pas supporté.',
            'recipient_email.required' => 'L\'adresse email du destinataire est nécessaire pour l\'envoi de l\'export.',
        ];
    }

    public function authorize(): bool
    {
        return auth()->user()->can('payroll.manage');
    }
}
