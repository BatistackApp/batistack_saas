<?php

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum DocumentType: string implements HasLabel
{
    case Quote = 'quote';
    case PurchaseOrder = 'purchase_order';
    case Delivery = 'delivery';
    case InvoiceProgress = 'invoice_progress';
    case InvoiceFinal = 'invoice_final';
    case Amendment = 'amendment';
    case CreditNote = 'credit_note';
    case Payment = 'payment';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Quote => __('commerce.document_type.quote'),
            self::PurchaseOrder => __('commerce.document_type.purchase_order'),
            self::Delivery => __('commerce.document_type.delivery'),
            self::InvoiceProgress => __('commerce.document_type.invoice_progress'),
            self::InvoiceFinal => __('commerce.document_type.invoice_final'),
            self::Amendment => __('commerce.document_type.amendment'),
            self::CreditNote => __('commerce.document_type.credit_note'),
            self::Payment => __('commerce.document_type.payment'),
        };
    }
}
