<?php

namespace App\Filament\Resources\OrderResource\Widgets;

use App\Models\Order;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OrderStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('New Orders', Order::where('status', 'new')->count())
                ->description('New Orders Waiting to be Processed')
                ->descriptionIcon('heroicon-m-clock')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('info'),
            Stat::make('Processing Orders', Order::where('status', 'processing')->count())
                ->description('Orders Curently being Processed')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('warning'),
            Stat::make('Completed Orders', Order::where('status', 'completed')->count())
                ->description('Orders Successfully')
                ->descriptionIcon('heroicon-m-check-badge')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('success'),
            Stat::make('Total Revenue', 'Rp' . number_format(Order::where('status', 'completed')->sum('total_payment'), 0), 0, ',', '.')
                ->description('Total Payment from Completed Orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
        ];
    }
}
