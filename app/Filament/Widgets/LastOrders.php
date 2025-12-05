<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use App\Models\Order;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class LastOrders extends BaseWidget
{
    protected static ?int $sort = 5;
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->latest()
                    ->take(5)
            )
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer Name')
                    ->sortable(),
                TextColumn::make('total_payment')
                    ->prefix('Rp')
                    ->numeric(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'new' => 'info',
                        'processing' => 'warning',
                        'completed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('date')
                    ->date()
                    ->sortable(),


            ])->paginated(false);
    }
}
