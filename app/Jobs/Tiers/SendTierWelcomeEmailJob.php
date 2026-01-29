<?php

namespace App\Jobs\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendTierWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Tiers $tier) {}

    public function handle(): void
    {
        if ($this->tier->email) {
            $this->tier->notify(new TierWelcomeNotification($this->tier));
        }
    }
}
