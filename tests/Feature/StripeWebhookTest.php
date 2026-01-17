<?php

use App\Enums\Core\InvoiceStatus;
use App\Models\Core\Invoice;
use Illuminate\Support\Str;

test('met à jour la facture après invoice.payment_succeeded', function () {
    $invoice = Invoice::factory()->create([
        'stripe_invoice_id' => Str::uuid()->toString(),
        'amount' => 0.0,
    ]);

    $payload = json_encode([
        'id' => Str::uuid()->toString(),
        'type' => 'invoice.payment_succeeded',
        'data' => [
            'object' => [
                'id' => $invoice->stripe_invoice_id,
                'amount_paid' => 12345,
                'paid' => true,
                'period_start' => now()->subMonth()->startOfMonth()->getTimestamp(),
                'period_end' => now()->subMonth()->endOfMonth()->getTimestamp(),
                'due_date' => now()->addDays(7)->getTimestamp(),
                'status_transitions' => [
                    'finalized_at' => now()->getTimestamp(),
                ],
            ],
        ],
    ]);

    $secret = config('services.stripe.webhook.secret');
    $timestamp = (int) now()->getTimestamp();
    $signature = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        [
            'HTTP_STRIPE_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $payload
    );

    $response->assertSuccessful();

    $invoice->refresh();

    expect($invoice->status)->toBe(InvoiceStatus::Paid)
        ->and((string) $invoice->amount)->toBe('123.45')
        ->and($invoice->billing_period_start)->not->toBeNull()
        ->and($invoice->billing_period_end)->not->toBeNull()
        ->and($invoice->paid_at)->not->toBeNull();
});

test('ignore si facture introuvable', function () {
    $payload = json_encode([
        'id' => Str::uuid()->toString(),
        'type' => 'invoice.payment_failed',
        'data' => [
            'object' => [
                'id' => 'inv_missing',
                'due_date' => now()->addDays(3)->getTimestamp(),
            ],
        ],
    ]);

    $secret = config('services.stripe.webhook.secret');
    $timestamp = (int) now()->getTimestamp();
    $signature = 't=' . $timestamp . ',v1=' . hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

    $response = $this->call(
        'POST',
        route('stripe.webhook'),
        [],
        [],
        [],
        [
            'HTTP_STRIPE_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ],
        $payload
    );

    $response->assertSuccessful();
});
