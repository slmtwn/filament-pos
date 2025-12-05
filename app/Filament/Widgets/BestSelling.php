<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use App\Models\OrderDetail;
use Illuminate\Support\Facades\DB;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Widgets\TableWidget as BaseWidget;

class BestSelling extends BaseWidget
{
    protected static ?int $sort = 4;
    protected static ?string $heading = 'Best Seller Products';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                OrderDetail::query()
                    ->select('product_id as id', 'product_id', DB::raw('SUM(qty) as total_sold'))
                    ->with('product')
                    ->groupBy('product_id')
                    ->orderByDesc('total_sold')
                    ->take(5)
            )
            ->columns([
                ImageColumn::make('product_image')
                    ->label('Image')
                    ->getStateUsing(fn(OrderDetail $record) => $record->product?->image ?? 'N/A'),
                TextColumn::make('product_name')
                    ->label('Name')
                    ->getStateUsing(fn(OrderDetail $record) => $record->product?->name ?? 'N/A'),
                TextColumn::make('total_sold')
                    ->label('Total Sold')
                    ->getStateUsing(fn(OrderDetail $record) => $record->total_sold ?? 0),
            ])->paginated(false);
    }
}
