<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Quotation;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Customers', Customer::count())
                ->description('Active customers')
                ->descriptionIcon('heroicon-m-users')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Total Quotations', Quotation::count())
                ->description('All time quotations')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),
            Stat::make('Pending Quotations', Quotation::where('status', 'draft')->count())
                ->description('Draft status')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
        ];
    }
}
