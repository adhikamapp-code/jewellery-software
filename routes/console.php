<?php

use App\Models\MetalRate;
use App\Services\JewelleryPriceCalculator;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('rates:sync {--branch= : Optional branch ID to update}', function () {
    $this->info('Fetching live bullion market prices...');

    try {
        $goldRes = Http::timeout(8)->get('https://api.gold-api.com/price/XAU');
        $silverRes = Http::timeout(8)->get('https://api.gold-api.com/price/XAG');
        $fxRes = Http::timeout(8)->get('https://open.er-api.com/v6/latest/USD');

        if (!$goldRes->successful() || !$fxRes->successful()) {
            $this->error('Failed to connect to bullion or currency feed.');
            return 1;
        }

        $inrRate = (float) ($fxRes->json()['rates']['INR'] ?? 87.5);
        $goldUsdPerOz = (float) $goldRes->json()['price'];
        $silverUsdPerOz = $silverRes->successful() ? (float) $silverRes->json()['price'] : 32.5;

        // 1 Troy Ounce = 31.1034768 grams
        $gold24kPerGram = round(($goldUsdPerOz * $inrRate) / 31.1034768, 2);
        $silver999PerGram = round(($silverUsdPerOz * $inrRate) / 31.1034768, 2);

        $branchId = $this->option('branch') ?: null;
        $now = now();

        MetalRate::create([
            'branch_id' => $branchId,
            'metal_type' => 'Gold',
            'purity' => '24K',
            'rate_per_gram' => $gold24kPerGram,
            'effective_at' => $now,
            'status' => 'approved',
            'notes' => 'Automated Live Market Feed',
        ]);

        foreach (['22K', '20K', '18K', '14K', '9K'] as $purity) {
            MetalRate::create([
                'branch_id' => $branchId,
                'metal_type' => 'Gold',
                'purity' => $purity,
                'rate_per_gram' => JewelleryPriceCalculator::deriveRateFrom24K($gold24kPerGram, $purity),
                'effective_at' => $now,
                'status' => 'approved',
                'notes' => 'Auto-calculated from Live 24K rate',
            ]);
        }

        MetalRate::create([
            'branch_id' => $branchId,
            'metal_type' => 'Silver',
            'purity' => '999',
            'rate_per_gram' => $silver999PerGram,
            'effective_at' => $now,
            'status' => 'approved',
            'notes' => 'Automated Live Market Feed',
        ]);

        $this->info("Successfully updated rates: 24K Gold @ ₹{$gold24kPerGram}/g, Silver 999 @ ₹{$silver999PerGram}/g");
        return 0;
    } catch (\Throwable $e) {
        $this->error('Error: ' . $e->getMessage());
        return 1;
    }
})->purpose('Fetch and update live metal rates from bullion market feed');

// Automatically run daily at 9:30 AM (bullion market opening)
Schedule::command('rates:sync')->dailyAt('09:30');
