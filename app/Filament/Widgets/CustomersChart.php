<?php

namespace App\Filament\Widgets;

use App\Models\Customer;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Filament\Widgets\ChartWidget;

class CustomersChart extends ChartWidget
{
    protected static ?string $heading = 'Customers Chart';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = Trend::model(Customer::class)
            ->between(
                start: now()->subMonth(6),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'Customers',
                    'data' => $data->map(fn(TrendValue $value) => $value->aggregate),
                ],
            ],
            'labels' => $data->map(fn(TrendValue $value) => date('M Y', strtotime($value->date)))->toArray(),
        ];
    }


    protected function getType(): string
    {
        return 'line';
    }
}
