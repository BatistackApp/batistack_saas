<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accounting\StoreAccountingEntryRequest;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\Journal;
use App\Services\Accounting\AccountingEntryService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountingEntryController extends Controller
{
    public function __construct(
        protected AccountingEntryService $entryService
    ) {}

    /**
     * Liste filtrée des écritures.
     */
    public function index(Request $request): JsonResponse
    {
        $query = AccountingEntry::with(['journal', 'lines.account'])
            ->withCount('lines');

        if ($request->has('journal_id')) {
            $query->where('journal_id', $request->journal_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json($query->latest('accounting_date')->paginate(30));
    }

    /**
     * Saisie d'une nouvelle écriture (OD, Factures manuelles).
     */
    public function store(StoreAccountingEntryRequest $request): JsonResponse
    {
        $journal = Journal::where('ulid', $request->journal_id)->firstOrFail();

        $entry = $this->entryService->create(
            $journal,
            Carbon::parse($request->accounting_date),
            $request->label,
            $request->lines,
            $request->description,
            $request->user()->id,
        );

        return response()->json($entry->load('lines'), 201);
    }

    /**
     * Détails d'une écriture et de ses lignes analytiques BTP.
     */
    public function show(string $ulid): JsonResponse
    {
        $entry = AccountingEntry::where('ulid', $ulid)
            ->with(['lines.account', 'lines.project', 'lines.phase'])
            ->firstOrFail();

        return response()->json($entry);
    }

    /**
     * Validation d'une écriture (Immuabilité fiscale).
     */
    public function validateEntry(string $ulid): JsonResponse
    {
        $entry = AccountingEntry::where('ulid', $ulid)->firstOrFail();

        try {
            $this->entryService->validate($entry);
            return response()->json(['message' => 'Écriture validée et inscrite définitivement au Grand Livre.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

}
