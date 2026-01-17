<?php

namespace App\Http\Controllers\Api;

use App\Enums\Core\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\Core\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('services.stripe.webhook.secret');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret, (int) config('services.stripe.webhook.tolerance', 300));
        } catch (SignatureVerificationException $e) {
            Log::warning('Stripe webhook signature invalid', ['message' => $e->getMessage()]);
            return response()->noContent(400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook parse error', ['message' => $e->getMessage()]);
            return response()->noContent(400);
        }

        match ($event->type) {
            'invoice.payment_succeeded' => $this->handlePaymentSucceeded($event->data->object),
            'invoice.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => null,
        };

        return response()->noContent();
    }

    protected function handlePaymentSucceeded(object $stripeInvoice): void
    {
        $invoice = Invoice::query()
            ->where('stripe_invoice_id', $stripeInvoice->id)
            ->first();

        if ($invoice === null) {
            return;
        }

        $invoice->forceFill([
            'amount' => (float) ($stripeInvoice->amount_paid / 100),
            'status' => InvoiceStatus::Paid,
            'issued_at' => optional($stripeInvoice->status_transitions)->finalized_at
                ? \Illuminate\Support\Carbon::createFromTimestamp($stripeInvoice->status_transitions->finalized_at)
                : $invoice->issued_at,
            'paid_at' => $stripeInvoice->paid ? now() : $invoice->paid_at,
            'billing_period_start' => \Illuminate\Support\Carbon::createFromTimestamp($stripeInvoice->period_start),
            'billing_period_end' => \Illuminate\Support\Carbon::createFromTimestamp($stripeInvoice->period_end),
            'due_at' => $stripeInvoice->due_date ? \Illuminate\Support\Carbon::createFromTimestamp($stripeInvoice->due_date) : $invoice->due_at,
        ])->save();
    }

    protected function handlePaymentFailed(object $stripeInvoice): void
    {
        $invoice = Invoice::query()
            ->where('stripe_invoice_id', $stripeInvoice->id)
            ->first();

        if ($invoice === null) {
            Log::warning('Stripe webhook: Invoice not found for payment_failed', ['stripe_invoice_id' => $stripeInvoice->id]);
            return;
        }

        $invoice->forceFill([
            'status' => InvoiceStatus::Void,
            'due_at' => $stripeInvoice->due_date ? \Illuminate\Support\Carbon::createFromTimestamp($stripeInvoice->due_date) : $invoice->due_at,
        ])->save();
    }
}
