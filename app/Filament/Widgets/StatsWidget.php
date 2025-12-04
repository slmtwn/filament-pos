<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Support\Enums\IconPosition;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Products', Product::count())
                ->description('Number of all products.')
                ->descriptionIcon('heroicon-o-shopping-cart', IconPosition::Before)
                ->chart([1, 5, 12, 8, 20])
                ->color('success'),
            Stat::make('Total Customers', Customer::count())
                ->description('Number of all customers.')
                ->descriptionIcon('heroicon-o-users', IconPosition::Before)
                ->chart([1, 5, 12, 8, 20])
                ->color('primary'),
            Stat::make('Total Orders', Order::count())
                ->description('Number of all orders.')
                ->descriptionIcon('heroicon-o-shopping-cart', IconPosition::Before)
                ->chart([1, 5, 12, 8, 20])
                ->color('success'),
            Stat::make('Total Revenue', 'Rp' . number_format(Order::where('status', 'completed')->sum('total_payment'), 0), 0, ',', '.')
                ->description('Total Payment from Completed Orders')
                ->descriptionIcon('heroicon-m-banknotes')
                ->chart([7, 2, 10, 3, 15, 4, 17])
                ->color('danger'),
        ];
    }
}
