<?php

namespace App\Services\Payroll;

use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\PayslipLine;
use Illuminate\Support\Facades\Storage;

class PayrollExportService
{
    /**
     * Génère un fichier CSV formaté pour l'import en comptabilité (ex: Sage/Cegid).
     */
    public function generateAccountingExport(PayrollPeriod $period): string
    {
        $fileName = 'export_paie_'.$period->id.'_'.now()->format('Ymd_His').'.csv';
        $filePath = 'tenants/'.$period->tenants_id.'/payroll/exports/'.$fileName;

        $handle = fopen('php://temp', 'r+');

        // En-têtes standards (Journal, Date, Compte, Libellé, Débit, Crédit)
        fputcsv($handle, ['Journal', 'Date', 'Compte', 'Référence', 'Libellé', 'Débit', 'Crédit']);

        $period->payslips()->with('lines', 'employee')->each(function ($payslip) use ($handle, $period) {
            foreach ($payslip->lines as $line) {
                // Logique de mapping simplifiée vers un plan comptable
                $account = $this->mapLineToAccount($line);
                $date = $period->end_date->format('d/m/Y');
                $label = "Paie {$period->name} - {$payslip->employee->last_name} - {$line->label}";

                fputcsv($handle, [
                    'OD',
                    $date,
                    $account,
                    'PAIE-'.$period->id,
                    $label,
                    $line->amount_gain > 0 ? $line->amount_gain : 0,
                    $line->amount_deduction > 0 ? $line->amount_deduction : 0,
                ]);
            }
        });

        rewind($handle);
        Storage::disk('public')->put($filePath, stream_get_contents($handle));
        fclose($handle);

        return $filePath;
    }

    /**
     * Mapping théorique vers les comptes comptables (641, 421, etc.)
     */
    protected function mapLineToAccount(PayslipLine $line): string
    {
        return match ($line->label) {
            'Salaire de base' => '641100',
            'Indemnité de repas' => '641400',
            'Retraite Complémentaire PRO BTP' => '437100',
            default => '641000'
        };
    }
}
