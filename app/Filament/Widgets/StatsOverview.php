<?php

namespace App\Filament\Widgets;

use App\Models\ChargeRange;
use App\Models\TransactionType;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $activeTypes = TransactionType::where('is_active', true)->count();
        $totalRanges = ChargeRange::where('is_active', true)->count();
        $uniqueTaxTypes = ChargeRange::distinct('tax_type')->count();

        return [
            Card::make('Active Transaction Types', $activeTypes)
                ->description('Total active transaction types')
                ->descriptionIcon('heroicon-s-credit-card')
                ->color('success'),

            Card::make('Active Charge Ranges', $totalRanges)
                ->description('Total active charge configurations')
                ->descriptionIcon('heroicon-s-calculator')
                ->color('primary'),

            Card::make('Tax Configurations', $uniqueTaxTypes)
                ->description('Different tax configurations')
                ->descriptionIcon('heroicon-s-currency-dollar')
                ->color('warning'),
        ];
    }
}
