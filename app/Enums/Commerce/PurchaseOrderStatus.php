<?php

namespace App\Enums\Commerce;

use BackedEnum;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum PurchaseOrderStatus: string implements HasColor, HasIcon, HasLabel
{
    case Draft = 'draft';
    case Sent = 'sent';
    case PartiallyReceived = 'partially_received';
    case Received = 'received';
    case Invoiced = 'invoiced';
    case Cancelled = 'cancelled';

    public function getColor(): string|array|null
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Sent => 'blue',
            self::PartiallyReceived => 'yellow',
            self::Received => 'green',
            self::Invoiced => 'indigo',
            self::Cancelled => 'red',
        };
    }

    public function getIcon(): string|BackedEnum|Htmlable|null
    {
        return match ($this) {
            self::Draft => 'heroicon-o-document-text',
            self::Sent => 'heroicon-o-paper-airplane',
            self::PartiallyReceived => 'heroicon-o-truck',
            self::Received => 'heroicon-o-check-circle',
            self::Invoiced => 'heroicon-o-currency-dollar',
            self::Cancelled => 'heroicon-o-x-circle',
        };
    }

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::Draft => __('commerce::purchase_order.statuses.draft'),
            self::Sent => __('commerce::purchase_order.statuses.sent'),
            self::PartiallyReceived => __('commerce::purchase_order.statuses.partially_received'),
            self::Received => __('commerce::purchase_order.statuses.received'),
            self::Invoiced => __('commerce::purchase_order.statuses.invoiced'),
            self::Cancelled => __('commerce::purchase_order.statuses.cancelled'),
        };
    }
}
