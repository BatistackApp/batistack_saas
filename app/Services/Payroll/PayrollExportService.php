<?php

namespace App\Services\Payroll;

use App\Enums\Payroll\PayrollExportFormat;
use App\Models\Core\Tenant;
use App\Models\Payroll\PayrollExport;
use App\Models\Payroll\PayrollSlip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PayrollExportService
{
    public function export(
        Tenant $company,
        int $year,
        int $month,
        PayrollExportFormat $format,
    ): PayrollExport {
        // Récupérer les fiches validées
        $slips = PayrollSlip::query()
            ->where('tenant_id', $company->id)
            ->where('year', $year)
            ->where('month', $month)
            ->whereIn('status', ['validated', 'exported'])
            ->with('employee')
            ->get();

        // Générer le contenu du fichier
        $content = match ($format) {
            PayrollExportFormat::Silae => $this->generateSilae($slips),
            PayrollExportFormat::Sage => $this->generateSage($slips),
            PayrollExportFormat::Generic => $this->generateGeneric($slips),
        };

        // Sauvegarder le fichier
        $fileName = "payroll_{$year}_{$month}_{$format->value}.csv";
        $path = "payroll/exports/{$company->id}/{$year}/{$month}/{$fileName}";

        Storage::disk('local')->put($path, $content);

        // Enregistrer dans la base de données
        $export = PayrollExport::create([
            'uuid' => Str::uuid(),
            'tenant_id' => $company->id,
            'format' => $format,
            'year' => $year,
            'month' => $month,
            'file_path' => $path,
            'file_name' => $fileName,
            'file_size' => strlen($content),
            'payroll_count' => $slips->count(),
            'exported_at' => now(),
        ]);

        // Mettre à jour le statut des fiches
        PayrollSlip::query()
            ->where('tenant_id', $company->id)
            ->where('year', $year)
            ->where('month', $month)
            ->update(['status' => 'exported', 'exported_at' => now()]);

        return $export;
    }

    /**
     * @param Collection<PayrollSlip> $slips
     */
    private function generateGeneric(Collection $slips): string
    {
        $rows = [
            ['Employee', 'Month', 'GrossAmount', 'SocialContributions', 'NetAmount', 'TransportAmount'],
        ];

        foreach ($slips as $slip) {
            $rows[] = [
                $slip->employee->name,
                "{$slip->year}-{$slip->month}",
                number_format($slip->gross_amount, 2, '.', ''),
                number_format($slip->social_contributions, 2, '.', ''),
                number_format($slip->net_amount, 2, '.', ''),
                number_format($slip->transport_amount, 2, '.', ''),
            ];
        }

        return $this->arrayToCsv($rows);
    }

    /**
     * @param Collection<PayrollSlip> $slips
     */
    private function generateSilae(Collection $slips): string
    {
        $rows = [
            ['[Import Silae Format]'],
            [],];

        foreach ($slips as $slip) {
            $rows[] = ["NumClient={$slip->company->siret}"];
            $rows[] = ["NumSalarie={$slip->employee->id}"];
            $rows[] = ["Mois={$slip->month}/{$slip->year}"];
            $rows[] = ["SalaireBrut=" . number_format($slip->gross_amount, 2, '.', '')];
            $rows[] = ["Charges=" . number_format($slip->social_contributions, 2, '.', '')];
            $rows[] = ["SalaireNet=" . number_format($slip->net_amount, 2, '.', '')];
            $rows[] = [];
        }

        return $this->arrayToCsv($rows);
    }

    /**
     * @param Collection<PayrollSlip> $slips
     */
    private function generateSage(Collection $slips): string
    {
        $rows = [
            ['[Écritures Sage]'],
            ['Journal=PAIE'],
            [],
        ];

        foreach ($slips as $slip) {
            $rows[] = ['Compte=6411'];
            $rows[] = ['Debit=' . number_format($slip->gross_amount, 2, '.', '')];
            $rows[] = ['LibellePiece=Paie ' . $slip->employee->name . ' ' . $slip->month . '/' . $slip->year];
            $rows[] = [];
        }

        return $this->arrayToCsv($rows);
    }

    /**
     * @param array<int, array<string>> $rows
     */
    private function arrayToCsv(array $rows): string
    {
        $csv = '';
        foreach ($rows as $row) {
            $csv .= '"' . implode('","', $row) . '"' . "\n";
        }

        return $csv;
    }
}
