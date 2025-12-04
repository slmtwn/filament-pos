<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Tables;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use App\Filament\Exports\OrderExporter;
use Filament\Forms\Components\Repeater;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\OrderResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use illuminate\Database\Eloquent\Relations\Relation;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\RelationManagers\OrderDetailRelationManager;

use Filament\Forms\Components\Actions\Action;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $activeNavigationIcon = 'heroicon-s-shopping-bag';

    public static function getGloballySearchableAttributes(): array
    {
        return ['id', 'customer.name'];
    }
    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Order ID' => $record->id ?? 'N/A',
            'Customer Name' => $record->customer?->name ?? 'N/A',
        ];
    }


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'new')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return static::getModel()::count() > 0 ? 'info' : 'primary';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'The number of new orders';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('date')
                    ->default(now())
                    ->required()
                    ->disabled()
                    ->hiddenLabel()
                    ->dehydrated()
                    ->prefix('Date Order')
                    ->columnSpanFull(),
                Group::make()
                    ->schema([
                        Section::make()
                            ->description('Customer Information')
                            ->schema([
                                Select::make('customer_id')
                                    ->relationship('customer', 'name')
                                    ->label('Name')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        $customer = Customer::find($state);
                                        $set('phone', $customer->phone ?? null);
                                        $set('address', $customer->address ?? null);
                                    })
                                    ->createOptionForm([
                                        TextInput::make('name')
                                            ->required()
                                            ->maxLength(255),
                                        TextInput::make('phone')
                                            ->tel()
                                            ->required()
                                            ->maxLength(255),
                                        Forms\Components\Textarea::make('address')
                                            ->required()
                                            ->columnSpanFull(),
                                    ]),
                                Placeholder::make('phone')
                                    ->content(fn(Get $get) => Customer::find($get('customer_id'))?->phone ?? '-'),
                                Placeholder::make('address')
                                    ->content(fn(Get $get) => Customer::find($get('customer_id'))?->address ?? '-'),
                            ])->columns(3),

                        Section::make()
                            ->description('Order Details')
                            ->schema([
                                Repeater::make('orderdetail')
                                    ->relationship()
                                    ->hiddenLabel()
                                    ->schema([
                                        Select::make('product_id')
                                            ->relationship('product', 'name', modifyQueryUsing: fn(Builder $query) => $query->where('is_active', true))
                                            ->reactive()
                                            ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $product = Product::find($state);
                                                $price = $product->price ?? 0;
                                                $set('price', $price);
                                                $qty = $get('qty') ?? 1;
                                                $set('qty', $qty);
                                                $subtotal = $price * $qty;
                                                $set('subtotal', $subtotal);

                                                $items = $get('../../orderdetail') ?? [];
                                                $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                                $set('../../total_price', $total);

                                                $discount = $get('../../discount');
                                                $discount_amount = $total * $discount / 100;
                                                $set('../../discount_amount', $discount_amount);
                                                $set('../../total_payment', $total - $discount_amount);
                                            })->searchable()
                                            ->columnSpanFull(),
                                        TextInput::make('price')
                                            ->readOnly()
                                            ->numeric()
                                            ->prefix('Rp')
                                            ->formatStateUsing(fn($state, Get $get) => $state ?? Product::find($get('product_id'))?->price ?? 0),
                                        TextInput::make('qty')
                                            ->numeric()
                                            ->default(1)
                                            ->reactive()
                                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                                $price = $get('price') ?? 0;
                                                $set('subtotal', $price * $state) ?? 0;

                                                $items = $get('../../orderdetail') ?? [];
                                                $total = collect($items)->sum(fn($item) => $item['subtotal'] ?? 0);
                                                $set('../../total_price', $total);

                                                $discount = $get('../../discount');
                                                $discount_amount = $total * $discount / 100;
                                                $set('../../discount_amount', $discount_amount);
                                                $set('../../total_payment', $total - $discount_amount);
                                            })
                                            ->minValue(1)
                                            ->maxValue(function (Get $get) {
                                                $productID = $get('product_id');
                                                $product = Product::find($productID);
                                                return $product->stock ?? 0;
                                            }),
                                        TextInput::make('subtotal')
                                            ->readOnly()
                                            ->prefix('Rp')
                                            ->default(0)
                                    ])->columns(3)
                                    ->addAction(
                                        fn(Action $action) => $action
                                            ->label('Add Product')
                                            ->color('primary')
                                            ->icon('heroicon-o-plus')
                                    ),
                            ]),

                    ])->columnSpan(2),
                Section::make()
                    ->description('Payment Information')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'new' => 'New',
                                'processing' => 'Processing',
                                'cancelled' => 'Cancelled',
                                'completed' => 'Completed'
                            ])->default('new')
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('total_price')
                            ->required()
                            ->numeric()
                            ->readOnly()
                            ->prefix('Rp')
                            ->columnSpanFull()
                            ->default(0),
                        TextInput::make('discount')
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->numeric()
                            ->columnSpan(2)
                            ->reactive()
                            ->suffix('%')
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                $discount = floatval($state) ?? 0;
                                $total_price = $get('total_price') ?? 0;
                                $discount_amount = $total_price * $discount / 100;
                                $set('discount_amount', $discount_amount);
                                $set('total_payment', $total_price - $discount_amount);
                            }),
                        TextInput::make('discount_amount')
                            ->columnSpan(2)
                            ->readOnly()
                            ->prefix('Rp')
                            ->default(0),
                        TextInput::make('total_payment')
                            ->columnSpanFull()
                            ->readOnly()
                            ->prefix('Rp')
                            ->default(0),
                        Select::make('payment_method')
                            ->columnSpan(2)
                            ->options([
                                'cash' => 'Cash',
                                'credit' => 'Credit',
                                'debet' => 'Debet',
                                'qris' => 'QRIS',
                                'va' => 'Virtual Account (VA)',
                            ])
                            ->default('cash'),
                        Select::make('payment_status')
                            ->columnSpan(2)
                            ->options([
                                'paid' => 'Paid',
                                'unpaid' => 'Unpaid',
                                'failed' => 'Failed',
                            ])
                            ->default('unpaid'),
                    ])->columnSpan(1)
                    ->columns(4),
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Order ID'),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer Name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->prefix('Rp')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('discount')
                    ->suffix('%')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('discount_amount')
                    ->prefix('Rp')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('total_payment')
                    ->prefix('Rp')
                    ->numeric(),
                TextColumn::make('payment_method'),
                TextColumn::make('payment_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'paid' => 'success',
                        'unpaid' => 'danger',
                    }),
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

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ])
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(OrderExporter::class)
                    ->label('Download Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('success'),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            OrderDetailRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
